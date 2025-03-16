@extends('layouts.administrator.master')

@push('css')
    <link rel="stylesheet" href="{{ asset('vendor/chart.js/Chart.min.css') }}">
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="row same-height">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="text-white">Performance Supplier {{ now()->format('F Y') }}</h4>
                    </div>
                    <div class="card-header bg-success text-white text-center">
                        <h5>TOP 3 OF THE MONTH</h5>
                    </div>
                    <div class="card-body">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="card-header bg-primary text-white text-center">BEST</h6>
                                    <ol class="list-group">
                                        <li class="list-group-item">1. PT. DIT</li>
                                        <li class="list-group-item">2. PT. CDC</li>
                                        <li class="list-group-item">3. PT. DCD</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="card-header bg-danger text-white text-center">WORST</h6>
                                    <ol class="list-group">
                                        <li class="list-group-item list-group-item-danger">1. PT. EEF</li>
                                        <li class="list-group-item list-group-item-danger">2. PT ARE</li>
                                        <li class="list-group-item list-group-item-danger">3. PT. FFG</li>
                                    </ol>
                                </div>
                            </div>

                        </div>
                        <div class="card-body">
                            <div class="row">
                                <canvas id="myChart2" height="842" width="1388"></canvas>
                                <canvas id="myChart" height="642" width="1388"></canvas>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-warning text-white text-center">
                        <h4 class="mb-0 text-white text-center">Quality Early Warning System</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>NO</th>
                                        <th>SUPPLIER</th>
                                        <th>OK RATIO</th>
                                        <th>NG RATIO</th>
                                        <th>JUDGEMENT</th>
                                        <th>PART NAME</th>
                                        <th>PROBLEM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($qualityWarnings as $warning)
                                    <tr class="{{ $warning['judgement'] == 'NG' ? 'table-danger' : '' }}">
                                        <td>{{ $warning['no'] }}</td>
                                        <td>{{ $warning['supplier'] }}</td>
                                        <td>{{ $warning['ok_ratio'] }}</td>
                                        <td>{{ $warning['ng_ratio'] }}</td>
                                        <td>{{ $warning['judgement'] }}</td>
                                        <td>{{ $warning['part_name'] ?? '-' }}</td>
                                        <td>{{ $warning['problem'] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-header bg-danger text-white text-center">
                            <h4 class="mb-0 text-white text-center">BAD NEWS FIRST</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="bg-danger text-white">
                                        <th>NO</th>
                                        <th>SUPPLIER</th>
                                        <th>PART NAME</th>
                                        <th>PROBLEM</th>
                                        <th>QTY</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>HASURA</td>
                                        <td>BOARD RR UPR RH</td>
                                        <td>IRAMURA</td>
                                        <td>ALL PART</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="row same-height">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white text-center">
                        <h4 class="mb-0 text-white text-center">LIST PROBLEM & FOLLOW UP CHECK RECEIVING INSPECTION</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>NO</th>
                                        <th>DATE</th>
                                        <th>PART NO</th>
                                        <th>PART NAME</th>
                                        <th>PROBLEM</th>
                                        <th>SUPPLIER</th>
                                        <th>CAR</th>
                                        <th>A3 REPORT</th>
                                        <th>FINDING LOC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($problems as $problem)
                                    <tr>
                                        <td>{{ $problem['no'] }}</td>
                                        <td>{{ $problem['date'] }}</td>
                                        <td>{{ $problem['part_no'] }}</td>
                                        <td>{{ $problem['part_name'] }}</td>
                                        <td>{{ $problem['problem'] }}</td>
                                        <td>{{ $problem['supplier'] }}</td>
                                        <td>{{ $problem['car'] }}</td>
                                        <td>{{ $problem['a3_report'] }}</td>
                                        <td>{{ $problem['finding_loc'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="row same-height">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Monthly Sales</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="myChart" height="642" width="1388"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="progress-wrapper">
                            <h4>Progress 25%</h4>
                            <div class="progress progress-bar-small">
                                <div class="progress-bar progress-bar-small" style="width: 25%" role="progressbar"
                                    aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="progress-wrapper">
                            <h4>Progress 45%</h4>
                            <div class="progress progress-bar-small">
                                <div class="progress-bar progress-bar-small bg-pink" style="width: 45%" role="progressbar"
                                    aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <canvas id="myChart2" height="842" width="1388"></canvas>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="row same-height">
            <div class="col-md-8">
                <div class="card">
                    <div class="header-statistics">
                        <h5>Monthly Statistics</h5>
                        <p>Based On Major Browser</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table small-font table-striped table-hover table-sm">
                                <tbody>
                                    <tr>
                                        <th scope="row">1</th>
                                        <td>Google Chrome</td>
                                        <td>5120</td>
                                        <td><i class="fa fa-caret-up text-success"></i></td>

                                    </tr>
                                    <tr>
                                        <th scope="row">2</th>
                                        <td>Mozilla Firefox</td>
                                        <td>4000</td>
                                        <td><i class="fa fa-caret-up text-success"></i></td>

                                    </tr>
                                    <tr>
                                        <th scope="row">3</th>
                                        <td>Safari</td>
                                        <td>8800</td>
                                        <td><i class="fa fa-caret-down text-danger"></i></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">3</th>
                                        <td>Opera Mini</td>
                                        <td>4123</td>
                                        <td><i class="fa fa-caret-up text-success"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Interest</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="myChart3" height="842" width="1388"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content-wrapper">
        <div class="row same-height">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div id="apex-chart"></div>
                    </div>
                </div>
                <br>
                <div class="card">
                    <span></span>

                    <div class="card-body">
                        <div id="apex-chart-bar"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Activities</h4>
                    </div>
                    <div class="card-body">
                        <ul class="timeline-xs">
                            <li class="timeline-item success">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        2 minutes ago
                                    </div>
                                    <p>
                                        <a class="text-info" href="">
                                            Bambang
                                        </a>
                                        has completed his account.
                                    </p>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        12:30
                                    </div>
                                    <p>
                                        Staff Meeting
                                    </p>
                                </div>
                            </li>
                            <li class="timeline-item danger">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        11:11
                                    </div>
                                    <p>
                                        Completed new layout.
                                    </p>
                                </div>
                            </li>
                            <li class="timeline-item info">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        Thu, 12 Jun
                                    </div>
                                    <p>
                                        Contacted
                                        <a class="text-info" href="">
                                            Microsoft
                                        </a>
                                        for license upgrades.
                                    </p>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        Tue, 10 Jun
                                    </div>
                                    <p>
                                        Started development new site
                                    </p>
                                </div>
                            </li>
                            <li class="timeline-item">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        Sun, 11 Apr
                                    </div>
                                    <p>
                                        Lunch with
                                        <a class="text-info" href="">
                                            Mba Inem
                                        </a>
                                        .
                                    </p>
                                </div>
                            </li>
                            <li class="timeline-item warning">
                                <div class="margin-left-15">
                                    <div class="text-muted text-small">
                                        Wed, 25 Mar
                                    </div>
                                    <p>
                                        server Maintenance.
                                    </p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Chat</h4>
                    </div>
                    <div class="card-body small-padding">
                        <div class="panel-discussion ps-chat">
                            <ol class="discussion">
                                <li class="messages-date">
                                    Sunday, Feb 9, 12:58
                                </li>
                                <li class="self">
                                    <div class="message">
                                        <div class="message-name">
                                            Mas Bambang
                                        </div>
                                        <div class="message-text">
                                            Hi, Mba Inem
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            How are you?
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="other">
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            Hi, i am good
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="self">
                                    <div class="message">
                                        <div class="message-name">
                                            Mas Bambang
                                        </div>
                                        <div class="message-text">
                                            Glad to see you ;)
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar1.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="messages-date">
                                    Sunday, Feb 9, 13:10
                                </li>
                                <li class="other">
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            What do you think about my new Dashboard?
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="messages-date">
                                    Sunday, Feb 9, 15:28
                                </li>
                                <li class="other">
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            Alo...
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            Are you there?
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="self">
                                    <div class="message">
                                        <div class="message-name">
                                            Mas Bambang
                                        </div>
                                        <div class="message-text">
                                            Hi, i am here
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar1.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            Your Dashboard is great
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar1.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="messages-date">
                                    Friday, Feb 7, 23:39
                                </li>
                                <li class="other">
                                    <div class="message">
                                        <div class="message-name">
                                            Mba Inem
                                        </div>
                                        <div class="message-text">
                                            How does the binding and digesting work in ReactJS?, Bang?
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar2.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                                <li class="self">
                                    <div class="message">
                                        <div class="message-name">
                                            Mas Bambang
                                        </div>
                                        <div class="message-text">
                                            oh that's your question?
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar1.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="message">
                                        <div class="message-name">
                                            Mas Bambang
                                        </div>
                                        <div class="message-text">
                                            little reduntant, no?
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar1.png') }}" alt="">
                                        </div>
                                    </div>
                                    <div class="message">
                                        <div class="message-name">
                                            Mas Bambang
                                        </div>
                                        <div class="message-text">
                                            literally we get the question daily
                                        </div>
                                        <div class="message-avatar">
                                            <img src="{{ asset('assets/images/avatar1.png') }}" alt="">
                                        </div>
                                    </div>
                                </li>
                            </ol>
                        </div>
                        <div class="message-bar">
                            <div class="message-inner">
                                <a class="link icon-only" href="#"><i class="fa fa-camera"></i></a>
                                <div class="message-area">
                                    <textarea placeholder="Message"></textarea>
                                </div>
                                <a class="link" href="#">
                                    Send
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('assets/js/pages/index.min.js') }}"></script>
@endpush
