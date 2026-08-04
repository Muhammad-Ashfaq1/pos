@extends('errors.layout')

@section('title', 'Forbidden')
@section('code', '403')
@section('heading', 'Access Denied')
@section('message', $exception->getMessage() ?: "You don't have permission to access this page.")
@section('illustration', asset('assets/img/illustrations/page-misc-you-are-not-authorized.png'))
@section('illustration_width', '170')
@section('illustration_class', 'mt-12')
