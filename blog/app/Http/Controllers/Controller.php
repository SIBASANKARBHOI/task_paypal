<?php

namespace App\Http\Controllers;

use App\Service;
use App\User;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Services\ExpressCheckout;
use Srmklive\PayPal\Services\AdaptivePayments;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * @Desc
     * @Class addUser
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @since 4 April 2019
     * @author Sibasankar Bhoi (sibasankarbhoi@globussoft.in)
     */
    public function signUp(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'username' => 'required |min:3|',
                'email' => 'required|email',
                'number' => 'required|numeric|min:10',
                'password' => 'required|min:3',
            ];
            $message = [
                'username.required' => 'Enter your User Name',
                'email.required' => 'Enter your Email',
                'number.required' => 'Enter your Phone Number',
                'password.required' => 'Enter your Password',
            ];

            $validator = validator::make($request->input(), $rules, $message);
            if ($validator->fails()) {
                return back()->WithErrors($validator)->WithInput();
            } else
                try {

                    $obj = new User();
//
                    $obj->username = $request->all()['username'];
                    $obj->email = $request->all()['email'];
                    $obj->password = Hash::make($request->all()['password']);
                    $obj->phone = $request->all()['number'];
                    $obj->save();
                    $usersId = $obj->id;
                    $sessionName = 'usersData';
                    $usersData = [];
                    $usersData['username'] = $request->all()['username'];
                    $usersData['email'] = $request->all()['email'];
                    $usersData['phone'] = $request->all()['number'];
                    $usersData['usersId'] = $usersId;
                    Session::put($sessionName, $usersData);
                    return redirect('/dashboard');
                } catch (\Exception $e) {
                    dd($e->getMessage());
                }
        }
        return view('addUser');

    }


    /**
     * @Desc
     * @Class signIn
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @since 04 April 2019
     * @author Sibasankar Bhoi (sibasankarbhoi@globussoft.in)
     */
    public function signIn(Request $request)
    {

        if ($request->isMethod('post')) {
            $rules = [
                'email' => 'required|email',
                'password' => 'required|min:3',
            ];
            $message = [
                'email.required' => 'Enter your Email',
                'password.required' => 'Enter your Password',
            ];

            $validator = validator::make($request->input(), $rules, $message);
            if ($validator->fails()) {
                return back()->WithErrors($validator)->WithInput();
            } else

                $email = $request->input('email');
            $password = $request->input('password');
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $userdata = json_decode(Auth::User(), true);
                $sessionName = 'usersData';
                Session::put($sessionName, $userdata);
                return redirect('/dashboard')->with('status', 'Wlcome to Dashboard');

            } else
                dd();
        }
        return view('login');
    }

    /**
     * @Desc
     * @Class dashboard
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @since 04 April 2019
     * @author Sibasankar Bhoi (sibasankarbhoi@globussoft.in)
     */
    public function dashboard(Request $request)
    {
        if (Session::has('usersData')) {
            $obj = new User();
            $usersId = \session('usersData')['usersId'];
            $usersDetails = json_decode(json_encode($obj::find($usersId), true), true);
            return view('dashboard', ['usersDetails' => $usersDetails]);
        } else
            return redirect('/addUser');
    }

    /**
     * @Desc
     * @Class updateUser
     * @param Request $request
     * @since 04 April 2019
     * @author Sibasankar Bhoi (sibasankarbhoi@globussoft.in)
     */
    public function updateUser(Request $request)
    {
        if ($request->isMethod('post')) {
            $rules = [
                'services' => 'required',
                'file' => 'required|max:10000|mimes:doc,docx'];
            $message = [
                'services.required' => 'Please choose a Service',
                'file.required' => 'Please Upload a File',
            ];

            $validator = validator::make($request->input(), $rules, $message);
            if ($validator->fails()) {
                return back()->WithErrors($validator)->WithInput();
            }
            $amount = $request->all()['services'];
            if ($amount == 100) {
                $purchaseData = 'editing';
            } elseif ($amount == 200) {
                $purchaseData = 'proofreading';
            } else {
                $purchaseData = 'Formatting';
            }
            $data = array(['name' => $purchaseData, 'amount' => $amount]);
            $obj = new Controller();
            $result = $obj->paypalInstant($data);
            $file = $request->file('file');
            $file_name = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $file_size = $file->getSize();
            //Move Uploaded File
            $destinationPath = 'uploads';
            $file->move($destinationPath, $file_name);
            $obj = new User();

            $user_id = \session('usersData')['usersId'];
            $data = [];
            $data['service'] = $request->all()['services'];
            $data['file'] = $file_name;
            $updatedData = $obj::where('id', $user_id)
                ->update(['service' => $request->all()['services'], 'file' => $file_name]);
            if ($updatedData) {
                return back()->with('status', 'You have successfully Update');
            }
        }
    }

    public function paypalInstant($purchaseDetails)
    {
        $provider = new ExpressCheckout;      // To use express checkout.
        $productNmae = $purchaseDetails[0]['name'];
        $productPrice = $purchaseDetails[0]['amount'];
        $data = [];
        $data['items'] = [
            [
                'name' => $productNmae,
                'price' => 1,
                'qty' => 1
            ]

        ];

        $data['invoice_id'] = 1;
        $data['invoice_description'] = "Invoice" . time();
        $data['return_url'] = url('/payment/success');
        $data['cancel_url'] = url('/cart');
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        $data['total'] = $total;
        $response = $provider->setExpressCheckout($data);
        // This will redirect user to PayPal


//        return redirect($response['paypal_link']);
        return back()->with('status', 'You have successfully Update');
    }

    public function paymentSuccess(Request $request)
    {
        $data = [];
        $data['items'] = [
            [
                'name' => 'Product 1',
                'price' => 1,
                'qty' => 1
            ]
        ];

        $data['invoice_id'] = 2;
        $data['invoice_description'] = "Invoice" . time();
        $data['return_url'] = url('/payment/success');
        $data['cancel_url'] = url('/cart');

        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }

        $data['total'] = $total;

//give a discount of 10% of the order amount
        $data['shipping_discount'] = round((10 / 100) * $total, 2);
        $token = $request->all()['token'];
        $payerId = $request->all()['PayerID'];
        $provider = new ExpressCheckout;      // To use express checkout.

        $response = $provider->getExpressCheckoutDetails($request->all()['token']);
        $fineResponse = $provider->doExpressCheckoutPayment($data, $token, $payerId);
        if ($fineResponse['ACK'] == 'Success') {
            return back();
        }

    }

    /**
     * @Desc
     * @Class logout
     * @since 04 April 2019
     * @author Sibasankar Bhoi (sibasankarbhoi@globussoft.in)
     */
    public function logout(){

        Session::flush();
        return redirect('/addUser');


    }
}
