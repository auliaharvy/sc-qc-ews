@extends('layouts.administrator.master')

@section('content')
    <x-form-section title="{{ $title }}">

        <form method="POST" action="{{ route('bad-news-firsts.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row mt-2">
                <div class="col-md-12">
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
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="problem">
                            Problem
                        </label>
                        <select class="form-select select2 @error('problem') is-invalid @enderror" id="problem"
                            name="problem" @required(true)>
                            <option value=""></option>
                            @foreach (getNgTypes() as $ngType)
                                <option value="{{ $ngType->name }}" {{ old('problem') == $ngType->name ? 'selected' : '' }}>
                                    {{ $ngType->name }}
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
                        <label for="qty">
                            Quantity
                        </label>
                        <input type="number" class="form-control @error('qty') is-invalid @enderror" id="qty"
                            name="qty" value="{{ old('qty') }}" @required(true)>
                        @error('qty')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="description">
                            Description
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                            name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
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
                <div class="col-md-6">
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
            <input type="hidden" name="issuance_date" id="issuance_date"
                                           class="form-control w-25"
                                           value="{{ request('issuance_date', now()->setTimezone('Asia/Jakarta')) }}"

                                           onchange="document.getElementById('dateFilterForm').submit()">
            <x-btn-submit-form />

        </form>

    </x-form-section>
@endsection
