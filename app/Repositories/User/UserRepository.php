<?php

namespace App\Repositories\User;

use LaravelEasyRepository\Repository;

interface UserRepository extends Repository
{
    public function index($request);
    public function store($request);
    public function show($id);
    public function update($request, $id);
    public function destroy($id);
    public function login($request);
    public function me($request);
}
