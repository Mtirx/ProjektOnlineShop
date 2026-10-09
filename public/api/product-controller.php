<?php

namespace App;

use mysqli;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProductController
{
    public function __construct(private mysqli $db)
    {
    }

    public function list(Request $request, Response $response): Response
    {
        $result = $this->db->query("SELECT * FROM product");

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        $response->getBody()->write(json_encode($products));

        return $response->withHeader("Content-Type", "application/json");
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $stmt = $this->db->prepare(
            "SELECT * FROM product WHERE product_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            $response->getBody()->write(json_encode([
                "error" => "Produkt nicht gefunden"
            ]));

            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $response->getBody()->write(json_encode($product));

        return $response->withHeader("Content-Type", "application/json");
    }


public function put(Request $request, Response $response, array $args): Response
{
    $id = (int) $args["id"];
    $data = json_decode($request->getBody()->getContents(), true);

    $active = (int) $data["active"];
    $id_category = (int) $data["id_category"];
    $name = $data["name"];
    $image = $data["image"];
    $description = $data["description"];
    $price = (float) $data["price"];
    $stock = (int) $data["stock"];
    
    $check = $this->db->prepare(
        "SELECT product_id, sku FROM product WHERE product_id = ?"
    );
    $check->bind_param("i", $id);
    $check->execute();
    $existingProduct = $check->get_result()->fetch_assoc();

    if ($existingProduct) {
        $stmt = $this->db->prepare(
            "UPDATE product
             SET active = ?, id_category = ?, name = ?, image = ?,
                 description = ?, price = ?, stock = ?
             WHERE product_id = ?"
        );

        $stmt->bind_param(
            "iisssdii",
            $active,
            $id_category,
            $name,
            $image,
            $description,
            $price,
            $stock,
            $id
        );

        $stmt->execute();

        $sku = $existingProduct["sku"];

        $message = "Produkt aktualisiert";
        $status = 200;
    } else {
        $sku = "PROD-" . $id;
        $stmt = $this->db->prepare(
            "INSERT INTO product
             (product_id, sku, active, id_category, name, image,
              description, price, stock)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isiisssdi",
            $id,
            $sku,
            $active,
            $id_category,
            $name,
            $image,
            $description,
            $price,
            $stock
        );

        $stmt->execute();

        $message = "Produkt erstellt";
        $status = 201;
    }

    $response->getBody()->write(json_encode([
        "message" => $message,
        "product_id" => $id,
        "sku" => $sku
    ]));

    return $response
        ->withStatus($status)
        ->withHeader("Content-Type", "application/json");
}
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $stmt = $this->db->prepare(
            "DELETE FROM product WHERE product_id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $response->getBody()->write(json_encode([
                "error" => "Produkt nicht gefunden"
            ]));

            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $response->getBody()->write(json_encode([
            "message" => "Produkt gelöscht"
        ]));

        return $response->withHeader("Content-Type", "application/json");
    }
}