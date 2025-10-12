@extends('backend.layouts.master')

@section('title')
    {{ localize('Limits Credit') }} {{ getSetting('title_separator') }} {{ getSetting('system_title') }}
@endsection

@section('contents')
    <section class="tt-section pt-4">
        <div class="container">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card tt-page-header">
                        <div class="card-body d-lg-flex align-items-center justify-content-lg-between">
                            <div class="tt-page-title">
                                <h2 class="h5 mb-lg-0">{{ localize('Limits Credit') }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4 g-4">
                <!--left sidebar-->
                <div class="col-xl-9 order-2 order-md-2 order-lg-2 order-xl-1">
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4 p-4" id="section-1">
                                
                                <form action="{{ route('admin.num_cuotas.store') }}" method="POST">
                                    @csrf
                                    
                                    <div class="row">
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('Numero de cuotas') }}</label>
                                            <input class="form-control" type="text" id="num_cuotas" name="num_cuotas" placeholder="{{ localize('Numero de cuotas') }}" value="{{ getSetting('num_cuotas') }}" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <button class="btn btn-primary" type="submit">
                                                    <i data-feather="save" class="me-1"></i> {{ localize('Save Cuotas') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-4 p-4" id="section-1">
                                
                                <form action="{{ route('admin.limits_credit.store') }}" class="pb-650" method="POST">
                                    @csrf
                                    @foreach (json_decode(getSetting('credit_limits')) as $key => $itm)
                                    
                                    <div class="row">
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('From') }}</label>
                                            <input class="form-control" type="text" id="from" name="limit[from][]" placeholder="{{ localize('From') }}" value="{{ $itm->from }}" required>
                                        </div>
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('To') }}</label>
                                            <input class="form-control" type="text" id="to" name="limit[to][]" placeholder="{{ localize('To') }}" value="{{ $itm->to }}" required>
                                        </div>
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('Limit') }}</label>
                                            <input class="form-control" type="text" id="limit" name="limit[limit][]" placeholder="{{ localize('Limit') }}" value="{{ $itm->limit }}" required>
                                        </div>
                                    </div>

                                    @endforeach

                                    <div class="row">
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('From') }}</label>
                                            <input class="form-control" type="text" id="from" name="limit[from][]" placeholder="{{ localize('From') }}" >
                                        </div>
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('To') }}</label>
                                            <input class="form-control" type="text" id="to" name="limit[to][]" placeholder="{{ localize('To') }}" >
                                        </div>
                                        <div class="mb-4 col-4">
                                            <label for="name" class="form-label">{{ localize('Limit') }}</label>
                                            <input class="form-control" type="text" id="limit" name="limit[limit][]" placeholder="{{ localize('Limit') }}" >
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <button class="btn btn-primary" type="submit">
                                                    <i data-feather="save" class="me-1"></i> {{ localize('Save Limits') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!--right sidebar-->
                <div class="col-xl-3 order-1 order-md-1 order-lg-1 order-xl-2">
                    <div class="card tt-sticky-sidebar">
                        <div class="card-body">
                            <h5 class="mb-4">{{ localize('Method Information') }}</h5>
                            <div class="tt-vertical-step">
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="#section-1" class="active">{{ localize('Limits') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
