@extends('layouts.app')

@section('panel_context', 'employee')

@push('styles')
    <style>
        .employee-shell-hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 36%),
                linear-gradient(135deg, #7367f0 0%, #5a4fd7 45%, #2f80ed 100%);
        }

        .employee-shell-hero::after {
            content: '';
            position: absolute;
            inset: auto -4rem -5rem auto;
            width: 13rem;
            height: 13rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .employee-shell-card {
            border: 1px solid rgba(115, 103, 240, 0.14);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .employee-shell-card:hover {
            transform: translateY(-2px);
            border-color: rgba(115, 103, 240, 0.28);
            box-shadow: 0 1rem 1.75rem rgba(47, 43, 61, 0.08);
        }

        .employee-shell-stat {
            background: linear-gradient(180deg, rgba(115, 103, 240, 0.08), rgba(115, 103, 240, 0.02));
            border: 1px solid rgba(115, 103, 240, 0.08);
        }

        .employee-shell-placeholder {
            border: 1px dashed rgba(47, 43, 61, 0.18);
            background: rgba(47, 43, 61, 0.02);
        }

        .employee-shell-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .employee-shell-toolbar .btn {
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {
            .employee-shell-toolbar {
                width: 100%;
            }

            .employee-shell-toolbar .btn {
                flex: 1 1 calc(50% - 0.5rem);
            }
        }
    </style>
@endpush
