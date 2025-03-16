@extends('layouts.administrator.master')

@section('content')
    <x-form-section title="{{ $title }}">

        <form method="POST" action="{{ route('problems.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="production_date">
                            Production Date
                        </label>
                        <input type="date" class="form-control @error('production_date') is-invalid @enderror"
                            id="production_date" name="production_date"
                            value="{{ old('production_date') }}" @required(true)>
                        @error('production_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="finding_location">
                            Finding Location
                        </label>
                        <input type="text" class="form-control @error('finding_location') is-invalid @enderror"
                            id="finding_location" name="finding_location"
                            value="{{ old('finding_location') }}" @required(true)>
                        @error('finding_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="part_id">
                            Part
                        </label>
                        <select class="form-select select2 @error('part_id') is-invalid @enderror" id="part_id"
                            name="part_id" @required(true)>
                            <option value=""></option>
                            @foreach (getPart() as $part)
                                <option value="{{ $part->id }}" {{ old('part_id') == $part->id ? 'selected' : '' }}>
                                    {{ $part->part_number }} - {{ $part->part_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('part_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity_affected">
                            Quantity
                        </label>
                        <input type="number" class="form-control @error('quantity_affected') is-invalid @enderror" id="quantity_affected"
                            name="quantity_affected" value="{{ old('quantity_affected') }}" @required(true)>
                        @error('quantity_affected')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="problem_description">
                            Problem
                        </label>
                        <textarea class="form-control @error('problem_description') is-invalid @enderror" id="problem_description"
                            name="problem_description" rows="5">{{ old('problem_description') }}</textarea>
                        @error('problem_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

            </div>

            @if(auth()->user()->roles->contains('name', 'Admin Supplier'))
            <input type="hidden" id="supplier_id" name="supplier_id" value="{{ auth()->user()->supplier_id }}" required readonly>
            @endif
            @if(auth()->user()->roles->contains('name', 'admin'))
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="supplier_id">
                            Supplier
                        </label>
                        <select class="form-select select2 @error('supplier_id') is-invalid @enderror" id="supplier_id"
                            name="supplier_id" @required(true)>
                            <option value=""></option>
                            @foreach (getSupplier() as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            @endif
            {{-- <input type="hidden" name="issuance_date" id="issuance_date"
                                           class="form-control w-25"
                                           value="{{ request('issuance_date', now()->setTimezone('Asia/Jakarta')) }}"

                                           onchange="document.getElementById('dateFilterForm').submit()"> --}}
            <x-btn-submit-form />

        </form>

    </x-form-section>
@endsection
