<?php

namespace App\Controllers;

use OpenApi\Generator;

class Swagger extends BaseController
{
    public function index()
    {
        return view('swagger/index');
    }

    public function generate(): string
    {
        $openapi = Generator::scan([APPPATH . 'Controllers']);
        $swaggerContent = $openapi->toJson();
        $filePath = FCPATH . 'swagger.json';
        file_put_contents($filePath, $swaggerContent);
        return $swaggerContent;
    }
}