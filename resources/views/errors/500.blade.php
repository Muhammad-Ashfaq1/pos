@extends('errors.layout')

@section('title', 'Server Error')
@section('code', '500')
@section('heading', 'Something went wrong')
@section('message', 'An unexpected error occurred. Please try again later, or contact support if it continues.')
@section('illustration', asset('assets/img/illustrations/page-misc-error.png'))
@section('illustration_width', '225')
