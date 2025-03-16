@extends('layouts.administrator.master')

@section('content')
    <x-form-section title="{{ $title }}">

        <form method="POST" action="{{ route('parts.update', $part->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="part_number">
                            Part Number
                        </label>
                        <input type="text" class="form-control @error('part_number') is-invalid @enderror" id="part_number"
                            name="part_number" value="{{ old('part_number', $part->part_number) }}" @required(true)>
                        @error('part_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="part_name">
                            Part Name
                        </label>
                        <input type="text" class="form-control @error('part_name') is-invalid @enderror" id="part_name"
                            name="part_name" value="{{ old('part_name', $part->part_name) }}" @required(true)>
                        @error('part_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="model">
                            Model
                        </label>
                        <input type="text" class="form-control @error('model') is-invalid @enderror" id="model"
                            name="model" value="{{ old('model', $part->model) }}" @required(true)>
                        @error('model')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sebango">
                            Sebango
                        </label>
                        <input type="text" class="form-control @error('sebango') is-invalid @enderror" id="sebango"
                            name="sebango" value="{{ old('sebango', $part->sebango) }}" @required(true)>
                        @error('sebango')
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
                                <option value="{{ $supplier->id }}" {{ $part->supplier_id == $supplier->id ? 'selected' : '' }}>
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
            <x-btn-submit-form />

        </form>

    </x-form-section>
@endsection
