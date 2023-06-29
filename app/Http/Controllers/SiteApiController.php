<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Category;
use App\Models\CompanyUser;
use App\Models\Feedback;
use App\Models\Rate;
use App\Models\Report;
use App\Models\SavedCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SiteApiController extends Controller
{
    public function home()
    {
        return view('homepage');
    }

    public function categories()
    {
        // TODO: query all categories
        $categories = Category::all();

        // return response()->json($categories);

        // return view('categoryshow', ['categories' => $categories]);
        return response()->json([
            'categories' => $categories,
        ],200);

    }

    public function categoryShowCompany($categoryName)
    {
        // TODO: query companies join with contacts, rate by category name
        $category_id = Category::where('name', $categoryName)->get();

        if (count($category_id) == 0) {
            // return view('category-show-company', [
            //     'cateNotFound' => true,
            //     'categoryName' => $categoryName,
            // ]);
            return response()->json([
                'cateNotFound' => true,
                'categoryName' => $categoryName
        ],200);
        } else {
            $category_id = $category_id[0]->category_id;
        }

        $companies = Company::with('contacts', 'rates')
            ->withAvg('rates as avg_star_rate', 'star_number')
            ->where([['category_id', $category_id], ['is_banned', 0]])->get();

        // return response()->json($companies);

        // return view('category-show-company', [
        //     'cateNotFound' => false,
        //     'companies' => $companies,
        //     'categoryName' => $categoryName,
        // ]);
        return response()->json([
            'cateNotFound' => false,
            'companies' => $companies,
            'categoryName' => $categoryName
        ],200);
    }

    public function companyDetail($categoryName, $companyName)
    {
        // TODO: query company by company name join with contacts, rates, feedbacks service rates
        // https://laravel.com/docs/8.x/eloquent-relationships#average-aggregate
        $company = Company::with('contacts', 'rates', 'feedbacks.normalUser', 'services', 'rates')
            ->withAvg('rates as avg_star_rate', 'star_number')
            ->where('name', $companyName)->first();

        // return response()->json($company);

        // return view('company-detail', [
        //     'company' => $company,
        //     'categoryName' => $categoryName,
        //     'companyName' => $companyName,
        // ]);
        return response()->json([
            'company' => $company,
            'categoryName' => $categoryName,
            'companyName' => $companyName,
            'currentUserRateNumber' => $currentUserRateNumber ? $currentUserRateNumber->star_number : null
        ],200);
    }

    public function search(Request $request)
    {
        $search_by = $request->input('search_by');
        $search_query = $request->input('search_query');
        $result = [];

        // if ($search_query) {
        if ($search_by == 'company') {
            $result = Company::with('contacts', 'rates', 'category')
                ->withAvg('rates as avg_star_rate', 'star_number')
                ->where([['name', 'like', '%' . $search_query . '%'], ['is_banned', 0]])->get();
        } else if ($search_by == 'category') {
            $result = Category::where('name', 'like', '%' . $search_query . '%')->get();
        }
        // }

        // return response()->json($result);

        // return view('search-results', [
        //     'search_query' => $search_query,
        //     'search_by' => $search_by,
        //     'result' => $result,
        // ]);
        return response()->json([
            'search_query' => $search_query,
            'search_by' => $search_by,
            'result' => $result
        ],200);
    }
}
