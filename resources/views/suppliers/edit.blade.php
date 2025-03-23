@extends('layouts.administrator.master')

@section('content')
    <x-form-section title="{{ $title }}">

        <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">
                            Nama Perusahaan
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                            name="name" value="{{ old('name', $supplier->name ) }}" @required(true)>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="code">
                            Kode Supplier
                        </label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                            name="code" value="{{ old('code', $supplier->code ) }}" @required(true)>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">
                            Email
                        </label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $supplier->email) }}" @required(true)>
                        <small class="text-muted">
                            Email Atasan / Supervisi PIC Supplier
                        </small>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="pic">
                            Nama
                        </label>
                        <input type="text" class="form-control @error('pic') is-invalid @enderror" id="email"
                            name="pic" value="{{ old('pic', $supplier->pic) }}" @required(true)>
                            <small class="text-muted">
                                Email Atasan / Supervisi PIC Supplier
                            </small>
                        @error('pic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <x-btn-submit-form />

        </form>

    </x-form-section>
@endsection
