<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
class ApiMain {
 public static function index(Request $request, Response $response, $args) {
 $response->getBody()->write("Hello, world!");
 return $response;
 }
}