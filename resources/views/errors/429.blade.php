@extends('errors.layout')

@section('title', 'Too Many Requests')
@section('code', '429')
@section('heading', 'Too Many Requests')
@section('message', 'You have made too many requests. Please wait a moment and try again.')
@section('illustration', asset('assets/img/illustrations/page-misc-error.png'))
@section('illustration_width', '225')
