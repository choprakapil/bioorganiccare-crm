<?php

use Illuminate\Http\Request;
use App\Models\User;

$user = User::where('role', 'doctor')->first();
auth()->login($user);

$req = Request::create('/api/clinical-catalog?view=my-services', 'GET');
$req->headers->set('Accept', 'application/json');
$req->setUserResolver(fn() => $user);
app(\App\Support\Context\TenantContext::class)->resolve($req);

$kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($req);

echo json_encode(json_decode($response->getContent(), true), JSON_PRETTY_PRINT);
