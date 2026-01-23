<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display the FAQ management page
     */
    public function index()
    {
        $faqs = Faq::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Store a new FAQ category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:255|unique:faqs,category',
            'questions_answers' => 'required|array|min:1',
            'questions_answers.*.question' => 'required|string',
            'questions_answers.*.answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $faq = Faq::create([
            'category' => $request->category,
            'questions_answers' => $request->questions_answers,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ category created successfully',
            'data' => $faq
        ]);
    }

    /**
     * Update an existing FAQ category
     */
    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:255|unique:faqs,category,' . $id,
            'questions_answers' => 'required|array|min:1',
            'questions_answers.*.question' => 'required|string',
            'questions_answers.*.answer' => 'required|string',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $faq->update([
            'category' => $request->category,
            'questions_answers' => $request->questions_answers,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'FAQ category updated successfully',
            'data' => $faq
        ]);
    }

    /**
     * Delete an FAQ category
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ category deleted successfully'
        ]);
    }

    /**
     * Toggle FAQ active status
     */
    public function toggleStatus($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'is_active' => $faq->is_active
        ]);
    }
}
