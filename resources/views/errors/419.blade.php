@extends('errors.layout')

@section('title', 'Page Expired')
@section('code', '419')
@section('heading', 'Page Expired')
@section('message', 'Your session has expired. Refresh the page and try again.')
@section('illustration', asset('assets/img/illustrations/page-misc-error.png'))
@section('illustration_width', '225')
@section('action_label', 'Refresh / go home')
