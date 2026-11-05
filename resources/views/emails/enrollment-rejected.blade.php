@extends('emails.layout')
@section('title', 'Your enrollment form needs changes')
@section('preview', 'Your adviser returned your enrollment form.')

@section('content')
    <p style="margin:0 0 4px;">Hello, {{ $student->greetingName() }}.</p>
    <p style="margin:0;">Your adviser returned your enrollment form. Sign in, correct it, and send it again.</p>
    @if($remarks)
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:18px 0 0;">
            <tr>
                <td style="padding:12px 14px; background-color:#fdf3f3; border-left:3px solid #b3262b; font-size:14px; color:#2b3442;">
                    <strong>What to fix:</strong> {{ $remarks }}
                </td>
            </tr>
        </table>
    @endif
    @include('emails.partials.button', ['url' => url('/login'), 'label' => 'Sign in and fix my form'])
@endsection
