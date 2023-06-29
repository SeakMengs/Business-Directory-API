<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\Report;
use App\Models\Feedback;
use App\Models\NormalUser;
use App\Models\SavedCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NormalUserApiController extends Controller
{
    protected $userData;

    public function __construct(Request $request)
    {
        // This check is for fetching data from api using api_token
        if ($request->header('Authorization')) {
            $api_token = $request->header('Authorization');

            $normalUserData = NormalUser::where('api_token', $api_token)->first();

            if ($normalUserData) {
                $this->userData = $normalUserData;
            }
        }
    }

    public function profile($username, $userId)
    {
        // TODO:: query normal_user where userId match userId join with savedCompany and company. the company must be joined with category
        // https://stackoverflow.com/questions/74097326/laravel-relationship-where-clause-returns-all-records-parent
        $data = NormalUser::with('savedCompanies.company.category')
            ->where('normal_user_id', $userId)->first();

        // return response()->json($data);

        if (!$data) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // return view('normal_user_profile', [
        //     'user' => $data,
        // ]);
        return response()->json([
            'user' => $data,
        ],200);
    }

    public function editProfile($username, $userId)
    {
        // TODO:: query normal_user where userId match userId
        $data = NormalUser::where('normal_user_id', $userId)->first();

        if (!$data) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        // return view('edit-normaluser-account', [
        //     'user' => $data,
        // ]);
        return response()->json([
            'user' => $data,
        ],200);

    }

    public function saveEditProfile(Request $request, $username, $userId)
    {

        // store the first two input field because by default we place value in the input field
        $storeInput = [
            'name' => $request->input('name'),
            'email' => $request->input('email')
        ];

        // if the change password check is checked
        // we validate password
        if (isset($request['change_password'])) {
            // add hash password to the storeInput array
            $storeInput['password'] = bcrypt($request->input('new_password'));

            // after knowing that the user want to change the password
            // we validate the password first
            $validatePassword = Validator::make(
                $request->input(),
                [
                    // password:companyUser is for custom validation rule
                    // it checks if the password is correct
                    // https://laravel.com/docs/8.x/validation#rule-password
                    'old_password' => ['required', 'min:8', 'password:normalUser'],
                    'new_password' => ['required', 'min:8', 'confirmed'],
                    'new_password_confirmation' => ['required', 'min:8'],
                ],
                [
                    'old_password.password' => 'The old password is incorrect',
                    'old_password.required' => 'The old password is required',
                    'old_password.min' => 'The old password must be at least 8 characters',
                    'new_password.required' => 'The new password is required',
                    'new_password.min' => 'The new password must be at least 8 characters',
                    'new_password.confirmed' => 'The new password confirmation does not match',
                    'new_password_confirmation.required' => 'The new password confirmation is required',
                    'new_password_confirmation.min' => 'The new password confirmation must be at least 8 characters',
                ]
            );

            if ($validatePassword->fails()) {
                return redirect()->back()->withErrors($validatePassword)->withInput($request->all());
            }
        }

        $validator = Validator::make(
            $request->input(),
            [
                // name must be unique but can be the same as the current name
                // https://stackoverflow.com/questions/22405762/laravel-update-model-with-unique-validation-rule-for-attribute
                'name' => ['required', 'max:255', 'unique:normal_user,name,' . $userId . ',normal_user_id'],
                'email' => ['required', 'email', 'max:255', 'unique:normal_user,email,' . $userId . ',normal_user_id'],
            ],
            [
                'name.required' => 'The name is required',
                'name.max' => 'The name must not exceed 255 characters',
                'name.unique' => 'The name is already taken',
                'email.required' => 'The email is required',
                'email.email' => 'The email is invalid',
                'email.max' => 'The email must not exceed 255 characters',
                'email.unique' => 'The email is already taken',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $saveChange = normalUser::where('normal_user_id', $userId)->update($storeInput);

        if (!$saveChange) {
            // return redirect()->back()->withErrors('error', 'Failed to save changes');
            return response()->json([
                'status'=>'error',
                'message' => 'Failed to save changes',
            ], 400);
        } else {
            // return redirect()->route('user.normal.name.id.profile.edit', [
            //     'name' => $storeInput['name'],
            //     'id' => $userId,
            // ])->with('success', 'Changes saved');
            return response()->json([
                'name' => $storeInput['name'],
                'id' => $userId,
                'status'=>'success',
                'message' => 'Changes saved',
            ], 200);
        }
    }

    public function removeSavedCompany(Request $request, $username, $userId)
    {
        $company_id = $request->query('company_id');

        if ($company_id) {
            // we check by user_id and company_id because we prevent other user to delete other user's saved company.
            $removeSavedCompany = SavedCompany::where([
                'normal_user_id' => $userId,
                'company_id' => $company_id,
            ])->delete();

            // return redirect()->back()->with('success', 'Company has been removed from your saved company list');
            return response()->json([
                'status'=>'success',
                'message' => 'Company has been removed from your saved company list',
            ], 200);
        } else {
            // return redirect()->back()->with('error', 'Remove saved company failed');
            return response()->json([
                'status'=>'error',
                'message' => 'Remove saved company failed',
            ], 400);
        }
    }

    public function saveCompany(Request $request, $categoryName, $companyName)
    {
        if (!$this->userData) {
            // return redirect()->back()->with('error', 'You must login as a normal user to save company');
            return response()->json([
                'status'=>'error',
                'message' => 'You must login as a normal user to save company',
            ], 400);
        }

        $company_id = $request->input('company_id');
        $currentUserId = $this->userData->normal_user_id;

        $checkSavedCompanyHistory = SavedCompany::where([
            ['normal_user_id', '=', $currentUserId],
            ['company_id', '=', $company_id],
        ])->first();

        if ($checkSavedCompanyHistory) {
            // return redirect()->back()->with('error', 'Company has already been saved in your list');
            return response()->json([
                'status'=>'error',
                'message' => 'Company has already been saved in your list',
            ], 400);
        }

        $saveCompany = SavedCompany::create([
            'normal_user_id' => $currentUserId,
            'company_id' => $company_id,
        ]);

        if ($saveCompany) {
            // return redirect()->back()->with('success', 'Company has been saved in your list');
            return response()->json([
                'status'=>'success',
                'message' => 'Company has been saved in your list',
            ], 200);
        } else {
            // return redirect()->back()->with('error', 'Failed to save company in your list');
            return response()->json([
                'status'=>'error',
                'message' => 'Failed to save company in your list',
            ], 400);
        }
    }

    public function postFeedback(Request $request, $categoryName, $companyName)
    {
        if (!$this->userData) {
            // return redirect()->back()->with('error', 'You must login as a normal user to post feedback on company');
            return response()->json([
                'status'=>'error',
                'message' => 'You must login as a normal user to post feedback on company',
            ], 400);
        }

        $validate = Validator::make($request->all(), [
            'feedback' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            if ($validate->errors()->has('feedback')) {
                // return redirect()->back()->with('error', 'Feedback is required');
                return response()->json([
                    'status'=>'error',
                    'message' => 'Feedback is required',
                ], 400);
            }
        }

        $feedback = $request->input('feedback');
        $company_id = $request->input('company_id');
        $currentUserId = $this->userData->normal_user_id;

        // Check if user has already posted feedback
        $checkFeedbackHistory = Feedback::where([
            ['normal_user_id', '=', $currentUserId],
            ['company_id', '=', $company_id],
        ])->first();

        if ($checkFeedbackHistory) {
            // return redirect()->back()->with('error', 'You have already posted feedback on this company before');
            return response()->json([
                'status'=>'error',
                'message' => 'You have already posted feedback on this company before',
            ], 400);
        }

        $saveFeedback = Feedback::create([
            'normal_user_id' => $currentUserId,
            'company_id' => $company_id,
            'feedback' => $feedback,
        ]);

        if ($saveFeedback) {
            // return redirect()->back()->with('success', 'Feedback has been posted');
            return response()->json([
                'status'=>'success',
                'message' => 'Feedback has been posted',
            ], 200);
        } else {
            // return redirect()->back()->with('error', 'Failed to post feedback');
            return response()->json([
                'status'=>'error',
                'message' => 'Failed to post feedback',
            ], 400);
        }
    }

    public function postReport(Request $request, $categoryName, $companyName)
    {
        if (!$this->userData) {
            // return redirect()->back()->with('error', 'You must login as a normal user to report company');
            return response()->json([
                'status'=>'error',
                'message' => 'You must login as a normal user to report company',
            ], 400);
        }

        $validate = Validator::make($request->all(), [
            'report' => ['required', 'string'],
        ]);

        if ($validate->fails()) {
            if ($validate->errors()->has('report')) {
                // return redirect()->back()->with('error', 'Report is required');
                return response()->json([
                    'status'=>'error',
                    'message' => 'Report is required',
                ], 400);
            }
        }

        $report = $request->input('report');
        $company_id = $request->input('company_id');
        $currentUserId = $this->userData->normal_user_id;

        // check if user has reported the company before
        $checkReportHistory = Report::where([
            ['report_by_normal_user_id', '=', $currentUserId],
            ['company_id', '=', $company_id],
        ])->first();

        if ($checkReportHistory) {
            // return redirect()->back()->with('error', 'You have already reported this company');
            return response()->json([
                'status'=>'error',
                'message' => 'You have already reported this company',
            ], 400);
        }

        $saveReport = Report::create([
            'report_by_normal_user_id' => $currentUserId,
            'company_id' => $company_id,
            'reason' => $report,
        ]);

        if ($saveReport) {
            // return redirect()->back()->with('success', 'Company has been reported');
            return response()->json([
                'status'=>'success',
                'message' => 'Company has been reported',
            ], 200);
        } else {
            // return redirect()->back()->with('error', 'Failed to report company');
            return response()->json([
                'status'=>'error',
                'message' => 'Failed to reported company',
            ], 400);
        }
    }

    public function postRate(Request $request, $categoryName, $companyName)
    {
        if (!$this->userData) {
            // return redirect()->back()->with('error', 'You must login as a normal user to rate company');
            return response()->json([
                'status'=>'error',
                'message' => 'You must login as a normal user to rate company',
            ], 400);
        }

        $validate = Validator::make($request->all(), [
            'rate_number' => ['required', 'string'],
        ]);

        $rate_number = $request->input('rate_number');
        $company_id = $request->input('company_id');
        $currentUserId = $this->userData->normal_user_id;

        if ($validate->fails()) {
            if ($validate->errors()->has('rate_number')) {
                // return redirect()->back()->with('error', 'Rate is required');
                return response()->json([
                    'status'=>'error',
                    'message' => 'Rate is required',
                ], 400);
            }
        }

        // check if user has rated this company before
        $checkRateHistory = Rate::where([
            ['normal_user_id', $currentUserId],
            ['company_id', $company_id],
        ])->first();

        // if user has rated this company before, update the rate
        if ($checkRateHistory) {
            $updateRating = Rate::where([
                ['normal_user_id', $currentUserId],
                ['company_id', $company_id],
            ])->update([
                        'star_number' => $rate_number,
                    ]);

            if ($updateRating) {
                // return redirect()->back()->with('success', 'Company rate has been updated to ' . $rate_number . ' stars');
                return response()->json([
                    'status'=>'success',
                    'message' => 'Company rate has been updated to ' . $rate_number . ' stars',
                ], 200);
            } else {
                // return redirect()->back()->with('error', 'Failed to rate company');
                return response()->json([
                    'status'=>'error',
                    'message' => 'Failed to rate company',
                ], 400);
            }
        }

        $saveRate = Rate::create([
            'normal_user_id' => $currentUserId,
            'company_id' => $company_id,
            'star_number' => $rate_number,
        ]);

        if ($saveRate) {
            // return redirect()->back()->with('success', 'Company has been rated');
            return response()->json([
                'status'=>'success',
                'message' => 'Company has been rated',
            ], 200);
        } else {
            // return redirect()->back()->with('error', 'Failed to rate company');
            return response()->json([
                'status'=>'error',
                'message' => 'Failed to rate company',
            ], 400);
        }
    }
}
