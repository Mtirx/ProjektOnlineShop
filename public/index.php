<?php

use Slim\Factory\AppFactory;
use App\CategoryController;
use App\ProductController;

require __DIR__ . "/../vendor/autoload.php";
require "api/api-main.php";
require "api/category-controller.php";
require "api/product-controller.php";

$db = new mysqli("localhost", "root", "", "lb1_uek295");

$db->set_charset("utf8mb4");
$app = AppFactory::create();
$app->setBasePath("/api/v1");

$app->get("/", [ApiMain::class, "index"]);
$categoryController = new CategoryController($db);

$app->get("/categories",[$categoryController, "list"]);
$app->get("/category/{id}",[$categoryController, "get"]);
$app->post("/category",[$categoryController, "post"]);
$app->patch("/category/{id}",[$categoryController, "patch"]);
$app->delete("/category/{id}",[$categoryController, "delete"]);

$productController = new ProductController($db);

$app->get("/products", [$productController, "list"]);
$app->get("/product/{id}", [$productController, "get"]);
$app->put("/product/{id}", [$productController, "put"]);
$app->delete("/product/{id}", [$productController, "delete"]);

$app->run();