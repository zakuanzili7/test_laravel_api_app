<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Logging added
use Illuminate\Support\Facades\Gate;

class BillingController extends Controller
{
    // Fetch all billings
    public function index(Request $request)
    {
        // Ensure the user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if the user is authorized to view billings
        if (Gate::denies('view-billings')) {
            
            Log::info('Unauthorized to view this collection billing');
            return response()->json(['message' => 'Unauthorized to view this collection billing'], 403);
        }

        // Retrieve all billings from the database
        $billings = Billing::all();
        Log::info('All Billings Retrieved: ', ['count' => $billings->count()]);

        return response()->json($billings); // Return as JSON response
    }

    // Fetch a specific billing by its code
    public function show($code, Request $request)
    {
        // Ensure the user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Check if the user is authorized to view this specific billing
        if (Gate::denies('view-billing', $code)) {
            return response()->json(['message' => 'Unauthorized to view this billing'], 403);
        }

        // Find the billing by code
        $billing = Billing::where('code', $code)->first();

        if (!$billing) {
            Log::error('Billing Not Found: ', ['code' => $code]);
            return response()->json(['message' => 'Billing not found'], 404);
        }

        Log::info('Billing Retrieved: ', ['code' => $code]);
        return response()->json($billing); // Return the billing record
    }

    // Create a new billing record
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:18|unique:billings,code',
            'belong_to_collection' => 'required|string',
            'status' => 'required|string',
            'amount' => 'required|numeric',
            'payment_description' => 'nullable|string',
            'payment_description2' => 'nullable|string',
            'due_date' => 'required|date',
            'payer_name' => 'required|string',
            'payer_email' => 'required|email',
            'payer_phone' => 'required|string',
            'payment_method' => 'required|string|in:OBW,MPGS,QR Pay',
        ]);

        $billing = Billing::create($request->all());  // Create a new billing record
        Log::info('Billing Created: ', ['code' => $billing->code]);

        return response()->json($billing, 201);  // Return the created billing as JSON
    }

    // Create a new billing record associated with a specific collection
    public function createBillingForCollection(Request $request, $collectionCode)
    {
        // Check if the collection exists
        $collection = Collection::where('code', $collectionCode)->first();

        if (!$collection) {
            Log::error('Collection Not Found: ', ['collection_code' => $collectionCode]);
            return response()->json(['message' => 'Collection not found'], 404);
        }

        // Validate the billing data
        $request->validate([
            'code' => 'required|string|size:18|unique:billings,code',
            'status' => 'required|string',
            'amount' => 'required|numeric',
            'payment_description' => 'nullable|string',
            'payment_description2' => 'nullable|string',
            'due_date' => 'required|date',
            'payer_name' => 'required|string',
            'payer_email' => 'required|email',
            'payer_phone' => 'required|string',
            'payment_method' => 'required|string|in:OBW,MPGS,QR Pay',
        ]);

        // Create the billing record with the collection's code
        $billing = Billing::create([
            'code' => $request->input('code'),
            'belong_to_collection' => $collectionCode,  // Associate with the collection
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
            'payment_description' => $request->input('payment_description'),
            'payment_description2' => $request->input('payment_description2'),
            'due_date' => $request->input('due_date'),
            'payer_name' => $request->input('payer_name'),
            'payer_email' => $request->input('payer_email'),
            'payer_phone' => $request->input('payer_phone'),
            'payment_method' => $request->input('payment_method'),
        ]);

        Log::info('Billing Created for Collection: ', ['code' => $billing->code, 'collection_code' => $collectionCode]);

        return response()->json($billing, 201); // Return the created billing as JSON
    }

    // Update an existing billing record
    public function update(Request $request, $code)
    {
        $billing = Billing::where('code', $code)->first();

        if (!$billing) {
            Log::error('Billing Not Found: ', ['code' => $code]);
            return response()->json(['message' => 'Billing not found'], 404);
        }

        $request->validate([
            'status' => 'required|in:paid,unpaid,expired',
            'amount' => 'numeric',
            'payment_description' => 'nullable|string',
            'payment_description2' => 'nullable|string',
            'due_date' => 'date',
            'payer_name' => 'string',
            'payer_email' => 'email',
            'payer_phone' => 'string',
            'payment_method' => 'required|in:OBW,MPGS,QR Pay',
        ]);
        
        $billing->update($request->all());  // Update the billing record
        Log::info('Billing Updated: ', ['code' => $code]);

        return response()->json($billing);  // Return the updated billing as JSON
    }

    // Delete a specific billing record
    public function destroy($code, Request $request)
    {
        $billing = Billing::where('code', $code)->first();

        if (!$billing) {
            Log::error('Billing Not Found: ', ['code' => $code]);
            return response()->json(['message' => 'Billing not found'], 404);
        }

        $billing->delete();  // Delete the billing record
        Log::info('Billing Deleted: ', ['code' => $code]);

        return response()->json(['message' => 'Billing deleted successfully']);
    }
}