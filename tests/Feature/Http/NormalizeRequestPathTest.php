<?php

use App\Http\Middleware\NormalizeRequestPath;
use Illuminate\Http\Request;

it('redirects request URIs with duplicate slashes', function () {
    $middleware = new NormalizeRequestPath;
    $request = Request::create('/admin', 'GET');
    $request->server->set('REQUEST_URI', '//admin');

    $response = $middleware->handle($request, fn () => response('ok'));

    expect($response->isRedirect())->toBeTrue()
        ->and($response->headers->get('Location'))->toEndWith('/admin');
});

it('passes through normal paths unchanged', function () {
    $middleware = new NormalizeRequestPath;
    $request = Request::create('/admin/login', 'GET');
    $called = false;

    $middleware->handle($request, function () use (&$called) {
        $called = true;

        return response('ok');
    });

    expect($called)->toBeTrue();
});
