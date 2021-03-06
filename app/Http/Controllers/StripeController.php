<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rules\In;

class StripeController extends Controller
{
    /**
     * Show the account page.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function ShowAccount(Request $request)
    {
        $user 		= $request->user();
        $invoices 	= $user->subscribed('main') ? $user->invoices() : null;
        return view('subscriptions.details', compact('user', 'invoices'));
    }

    /**
     * Process the subscription.
     *
     * @param Request $request
     * @param $plan
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function processSubscribe(Request $request, $plan)
    {
        $user       = $request->user();
        $ccToken    = $request->input('stripeToken');
        $planId     = $plan;

        $user->newSubscription('main', $planId)->create($ccToken, [
            'email' => $user->email
        ]);

        return redirect('/user/subscription/details')->with('message', 'Now you are Premium :)');
    }

    /**
     * Update the subscription.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSubscription(Request $request)
    {

        $user = $request->user();
        $plan = $request->input('plan');

        if ($user->subscribed('main') and $user->subscription('main')->onGracePeriod())
        {
            if($user->onPlan($plan))
            {
                $user->subscription('main')->resume();
            }

            $user->subscription('main')->resume()->swap($plan);
        }

        $user->subscription('main')->swap($plan);

    	return redirect()->back()->with(['success' => 'Subscription updated.']);

    }

    /**
     * Update the credit card.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCard(Request $request)
    {
    	$user     = $request->user();
        $ccToken  = $request->input('stripeToken');
    	$user->updateCard($ccToken);

    	return redirect()->back()->with(['success' => 'Credit card updated.']);
    }

    /**
     * Download an invoice.
     *
     * @param $invoiceId
     * @return
     */
    public function downloadInvoice($invoiceId)
    {
        return Auth::user()->downloadInvoice($invoiceId, [
            'vendor' => 'Company Name',
            'product'=> 'Subscriptions'
        ]);
    }

    /**
     * Delete Subscription.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSubscription(Request $request)
    {
    	$user = $request->user();
        $user->subscription('main')->cancel();

        return redirect()->back()->with(['success' => 'Subscription cancelled.']);
    }


}
