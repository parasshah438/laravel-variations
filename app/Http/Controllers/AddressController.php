<?php

namespace App\Http\Controllers;

use App\Models\UserAddress;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{

    /**
     * Display a listing of user addresses
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()
                        ->with(['country', 'state', 'city'])
                        ->active()
                        ->orderBy('is_default', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address
     */
    public function create()
    {
        $countries = Country::active()->orderBy('name')->get();
        return view('addresses.create', compact('countries'));
    }

    /**
     * Store a newly created address
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:home,work,other',
            'label' => 'nullable|string|max:255',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'postal_code' => 'required|string|max:10',
            'gst_number' => 'nullable|string|max:15',
            'is_default' => 'boolean',
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
            'delivery_instructions' => 'nullable|string'
        ]);

        $validated['user_id'] = Auth::id();

        // If this is the user's first address, make it default
        if (Auth::user()->addresses()->count() === 0) {
            $validated['is_default'] = true;
            $validated['is_default_shipping'] = true;
            $validated['is_default_billing'] = true;
        }

        UserAddress::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address added successfully',
                'redirect' => route('addresses.index')
            ]);
        }

        return redirect()->route('addresses.index')
                        ->with('success', 'Address added successfully');
    }

    /**
     * Show the form for editing an address
     */
    public function edit(UserAddress $address)
    {
        $this->authorize('update', $address);
        
        $countries = Country::active()->orderBy('name')->get();
        $states = State::where('country_id', $address->country_id)->active()->orderBy('name')->get();
        $cities = City::where('state_id', $address->state_id)->active()->orderBy('name')->get();

        return view('addresses.edit', compact('address', 'countries', 'states', 'cities'));
    }

    /**
     * Update an address
     */
    public function update(Request $request, UserAddress $address)
    {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'type' => 'required|in:home,work,other',
            'label' => 'nullable|string|max:255',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'postal_code' => 'required|string|max:10',
            'gst_number' => 'nullable|string|max:15',
            'is_default' => 'boolean',
            'is_default_shipping' => 'boolean',
            'is_default_billing' => 'boolean',
            'delivery_instructions' => 'nullable|string'
        ]);

        $address->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully'
            ]);
        }

        return redirect()->route('addresses.index')
                        ->with('success', 'Address updated successfully');
    }

    /**
     * Remove an address
     */
    public function destroy(UserAddress $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    /**
     * Set default address
     */
    public function setDefault(Request $request, UserAddress $address)
    {
        $this->authorize('update', $address);

        $type = $request->get('type', 'default'); // default, shipping, billing

        switch ($type) {
            case 'shipping':
                $address->update(['is_default_shipping' => true]);
                break;
            case 'billing':
                $address->update(['is_default_billing' => true]);
                break;
            default:
                $address->update(['is_default' => true]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully'
        ]);
    }

    /**
     * Get states by country
     */
    public function getStates($countryId)
    {
        $states = State::where('country_id', $countryId)
                      ->active()
                      ->orderBy('name')
                      ->get(['id', 'name']);

        return response()->json($states);
    }

    /**
     * Get cities by state
     */
    public function getCities($stateId)
    {
        $cities = City::where('state_id', $stateId)
                     ->active()
                     ->orderBy('name')
                     ->get(['id', 'name', 'postal_code_prefix']);

        return response()->json($cities);
    }

    /**
     * Quick add address (for checkout)
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'postal_code' => 'required|string|max:10',
            'type' => 'required|in:home,work,other'
        ]);

        $validated['user_id'] = Auth::id();

        $address = UserAddress::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'address' => [
                'id' => $address->id,
                'formatted_address' => $address->formatted_address,
                'short_address' => $address->short_address,
                'full_name' => $address->full_name,
                'phone_number' => $address->phone_number
            ]
        ]);
    }
}
