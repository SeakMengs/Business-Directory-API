<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminApiController;
use App\Http\Controllers\NormalUserApiController;
use App\Http\Controllers\CompanyUserApiController;
use App\Http\Controllers\SiteApiController;
use App\Http\Controllers\auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(SiteApiController::class)->group(function () {

    // Route::get('/', 'home')->name('home');

    Route::get('/category', 'categories')->name('category');

    route::get('/category/{categoryName}', 'categoryShowCompany')->name('category.categoryName');

    route::get('/category/{categoryName}/{companyName}', 'companyDetail')->name('category.categoryName.companyName');

    route::get('/search', 'search')->name('search');

});
// Group route by RegisterController
Route::controller(RegisterApiController::class)->group(function() {

    // Route::get('/sign-up', 'signUpOption')->name('sign-up');

    // Route::get('sign-up/company', 'companyUserSignUpView')->name('sign-up.company');

    // Route::get('sign-up/user', 'normalUserSignUpView')->name('sign-up.user');

    // Route for saving user to database
    Route::post('/register/company', 'companyUserRegister')->name('register.company');

    Route::post('/register/user', 'normalUserRegister')->name('register.user');

});

Route::middleware(['companyAuth:companyUser'])->group(function () {
    Route::controller(CompanyUserApiController::class)->group(function () {

        Route::get('/user/company/{name}/{id}/profile', 'profile')->name('user.company.name.id.profile');

        Route::get('/user/company/{name}/{id}/profile/edit', 'editProfile')->name('user.company.name.id.profile.edit');

        Route::get('/user/company/{name}/{id}/add-company', 'addCompany')->name('user.company.name.id.add-company');

        Route::get('/user/company/{name}/{id}/edit-company', 'editCompany')->name('user.company.name.id.edit-company');

        Route::get('/user/company/{name}/{id}/remove-company', 'removeCompany')->name('user.company.name.id.removeCompany');

        //----------------POST METHOD ROUTE ---------------------------------------------------- */

        Route::post('/user/company/{name}/{id}/profile/edit/save', 'saveEditProfile')->name('user.company.name.id.profile.edit.save');

        Route::post('/user/company/{name}/{id}/add-company/save', 'addCompanySave')->name('user.company.name.id.add-company.save');

        //----------------PATCH METHOD ROUTE ---------------------------------------------------- */

        Route::patch('/user/company/{name}/{id}/edit-company/save', 'saveEditCompany')->name('user.company.name.id.edit-company.save');

    });
});

Route::middleware(['userAuth:normalUser'])->group(function () {
    Route::controller(NormalUserApiController::class)->group(function () {

        Route::get('/user/normal/{name}/{id}/profile', 'profile')->name('user.normal.name.id.profile');

        Route::get('/user/normal/{name}/{id}/profile/edit', 'editProfile')->name('user.normal.name.id.profile.edit');

        //----------------POST METHOD ROUTE ---------------------------------------------------- */

        Route::post('/user/normal/{name}/{id}/profile/edit/save', 'saveEditProfile')->name('user.normal.name.id.profile.edit.save');

        Route::post('/user/normal/{name}/{id}/remove-saved-company', 'removeSavedCompany')->name('user.normal.name.id.remove-saved-company');

        Route::post('/category/{categoryName}/{companyName}/save', 'saveCompany')->name('category.categoryName.companyName.save');

        Route::post('/category/{categoryName}/{companyName}/feedback/post', 'postFeedback')->name('category.categoryName.companyName.feedback.post');

        Route::post('/category/{categoryName}/{companyName}/report/post', 'postReport')->name('category.categoryName.companyName.report.post');

        Route::post('/category/{categoryName}/{companyName}/rate/post', 'postRate')->name('category.categoryName.companyName.rate.post');

    });
});

Route::middleware(['adminAuth:adminUser'])->group(function () {
    Route::controller(AdminApiController::class)->group(function () {

        Route::get('/admin/user', 'getUser')->name('api.admin.getUser');

        // Not All Route can be accessed by plain route, some route need to pass query string
        // Example: http://127.0.0.1:8000/api/admin/acc-management/normalUsers?sortOrderBy=desc&query=&searchBy=name&banByAdminId=
        Route::get('/admin/website-overview', 'getWebsiteOverview')->name('api.admin.web-overview');

        Route::get('/admin/acc-management/normalUsers', 'getNormalUsers')->name('api.admin.acc-management.normalUsers');

        Route::get('/admin/acc-management/companyUsers', 'getCompanyUsers')->name('api.admin.acc-management.companyUsers');

        Route::get('/admin/acc-management/companies', 'getCompanies')->name('api.admin.acc-management.companies');

        Route::get('/admin/site-management/category', 'getCategory')->name('api.admin.site-management.category');

        Route::get('/admin/admin-management/admins', 'getAdmins')->name('api.admin.admin-management.admins');

        //----------------POST METHOD ROUTE ---------------------------------------------------- */

        Route::post('/admin/acc-management/post/banCompany', 'banCompany')->name('api.admin.acc-management.post.banCompany');

        Route::post('/admin/acc-management/post/banCompanyUser', 'banCompanyUser')->name('api.admin.acc-management.post.banCompanyUser');

        Route::post('/admin/acc-management/post/banNormalUser', 'banNormalUser')->name('api.admin.acc-management.post.banNormalUser');

        Route::post('/admin/acc-management/post/unBanCompanyUser', 'unBanCompanyUser')->name('api.admin.acc-management.post.unBanCompanyUser');

        Route::post('/admin/acc-management/post/unBanNormalUser', 'unBanNormalUser')->name('api.admin.acc-management.post.unBanNormalUser');

        Route::post('/admin/site-management/post/addCategory', 'addCategory')->name('api.admin.site-management.post.addCategory');

        Route::post('/admin/site-management/post/updateCategory', 'updateCategory')->name('api.admin.site-management.post.updateCategory');

        Route::post('/admin/site-management/post/removeCategory', 'removeCategory')->name('api.admin.site-management.post.removeCategory');

        Route::post('/admin/admin-management/post/createAdmin', 'createAdmin')->name('api.admin.admin-management.post.createAdmin');

        Route::post('/admin/admin-management/post/updateAdmin', 'updateAdmin')->name('api.admin.admin-management.post.updateAdmin');

        Route::post('/admin/admin-management/post/removeAdmin', 'removeAdmin')->name('api.admin.admin-management.post.removeAdmin');

        Route::post('/admin-management/post/admin/reset-default-password', 'resetDefaultAdminPassword')->name('api.admin.admin-management.post.resetDefaultAdminPassword');

        Route::post('/admin-management/post/profile/updateAdminProfile', 'updateAdminProfile')->name('api.admin.admin-management.post.updateAdminProfile');

    });
});
