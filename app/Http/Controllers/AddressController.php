<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $query = Address::query();
        if (! $request->user()->hasRole('admin')) {
            $query->where('user_id', $request->user()->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        $addresses = $query->latest()->paginate(15);

        return view('addresses.index', compact('addresses'));
    }

    public function create(Request $request)
    {
        $userId = $request->user()->hasRole('admin') && $request->filled('user_id')
            ? $request->user_id
            : $request->user()->id;

        return view('addresses.create', compact('userId'));
    }

    public function store(StoreAddressRequest $request)
    {
        $data = $request->validated();
        if (! $request->user()->hasRole('admin')) {
            $data['user_id'] = $request->user()->id;
        }
        $data['is_default'] = $request->boolean('is_default', true);

        Address::create($data);

        return redirect()->route('addresses.index')->with('success', 'Address created successfully.');
    }

    public function show(Address $address)
    {
        $this->authorizeAddress($address);

        return view('addresses.show', compact('address'));
    }

    public function edit(Address $address)
    {
        $this->authorizeAddress($address);

        return view('addresses.edit', compact('address'));
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        $this->authorizeAddress($address);

        $data = $request->validated();
        if ($request->has('is_default')) {
            $data['is_default'] = $request->boolean('is_default');
        }

        $address->update($data);

        return redirect()->route('addresses.show', $address)->with('success', 'Address updated successfully.');
    }

    public function destroy(Address $address)
    {
        $this->authorizeAddress($address);

        $address->delete();

        return redirect()->route('addresses.index')->with('success', 'Address deleted successfully.');
    }

    protected function authorizeAddress(Address $address): void
    {
        if ($address->user_id !== request()->user()->id && ! request()->user()->hasRole('admin')) {
            abort(Response::HTTP_FORBIDDEN, 'You can only manage your own addresses.');
        }
    }
}
