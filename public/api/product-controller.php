<?php

namespace App;

use mysqli;
use OpenApi\Attributes\Put;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OAT;

class ProductController
{
    public function __construct(private mysqli $db)
    {
    }

    #[OAT\Get(
        path: '/api/v1/products',
        operationId: 'productlist',
        summary: 'Alle Produkte anzeigen',
        tags: ['Products'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Liste aller Produkte',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        type: 'object',
                        properties: [
                        new OAT\Property(property: 'product_id', type: 'string', example: '12345678'),
                        new OAT\Property(property: 'sku', type: 'string', example: 'PROD-12345678'),
                        new OAT\Property(property: 'active', type: 'string', example: '1'),
                        new OAT\Property(property: 'id_category', type: 'string', example: '2'),
                        new OAT\Property(property: 'name', type: 'string', example: 'New-CsBe-Logo'),
                        new OAT\Property(property: 'image', type: 'string', example: 'https://www.csbe.ch/resources/themes/csbe/images/logo.svg?m=1595406300'),
                        new OAT\Property(property: 'description', type: 'string', example: 'Kaufen Sie jetzt das tolle Logo der CsBe!'),
                        new OAT\Property(property: 'price', type: 'string', example: '39999.95'),
                        new OAT\Property(property: 'stock', type: 'string', example: '10'),
                        ]
                    )
                )
            )
        ]
    )]
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

        #[OAT\Get(
        path: '/product/{id}',
        operationId: 'productsingle',
        summary: 'Produkt durch ID anzeigen',
        tags: ['Products'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Produkt Info',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        type: 'object',
                        properties: [
                        new OAT\Property(property: 'product_id', type: 'string', example: '12345678'),
                        new OAT\Property(property: 'sku', type: 'string', example: 'PROD-12345678'),
                        new OAT\Property(property: 'active', type: 'string', example: '1'),
                        new OAT\Property(property: 'id_category', type: 'string', example: '2'),
                        new OAT\Property(property: 'name', type: 'string', example: 'New-CsBe-Logo'),
                        new OAT\Property(property: 'image', type: 'string', example: 'https://www.csbe.ch/resources/themes/csbe/images/logo.svg?m=1595406300'),
                        new OAT\Property(property: 'description', type: 'string', example: 'Kaufen Sie jetzt das tolle Logo der CsBe!'),
                        new OAT\Property(property: 'price', type: 'string', example: '39999.95'),
                        new OAT\Property(property: 'stock', type: 'string', example: '10'),
                        ]
                    )
                )
            ),
            new OAT\Response(
                response: 404,
                description:'Produkt nicht gefunden',
                content: new OAT\JsonContent(
                    type:'object',
                    properties: [
                        new OAT\Property(property: 'error', type: 'intiger', example:'Produkt nicht gefunden'),
                    ]
                )
            )
        ]
    )]
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

    #[OAT\Put(
        path: '/api/v1/product/{id}',
        operationId: 'productcreateorupdate',
        summary: 'Produkt erstellen oder aktualisieren',
        tags: ['Products'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                type: 'object',
                properties: [
                    new OAT\Property(property: 'product_id', type: 'string', example: '12345678'),
                    new OAT\Property(property: 'sku', type: 'string', example: 'PROD-12345678'),
                    new OAT\Property(property: 'active', type: 'string', example: '1'),
                    new OAT\Property(property: 'id_category', type: 'string', example: '2'),
                    new OAT\Property(property: 'name', type: 'string', example: 'New-CsBe-Logo'),
                    new OAT\Property(property: 'image', type: 'string', example: 'https://www.csbe.ch/resources/themes/csbe/images/logo.svg?m=1595406300'),
                    new OAT\Property(property: 'description', type: 'string', example: 'Kaufen Sie jetzt das tolle Logo der CsBe!'),
                    new OAT\Property(property: 'price', type: 'string', example: '39999.95'),
                    new OAT\Property(property: 'stock', type: 'string', example: '10'),
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Produkt erstellt',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Produkt erstellt'),
                        new OAT\Property(property: 'product_id', type: 'integer', example: 12345679),
                        new OAT\Property(property: 'sku', type: 'string', example: 'PROD-12345679'),
                    ]
                )
            ),
            new OAT\Response(
                response: 200,
                description: 'Produkt aktualisiert',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Produkt aktualisiert'),
                        new OAT\Property(property: 'product_id', type: 'integer', example: 12342679),
                        new OAT\Property(property: 'sku', type: 'string', example: 'PROD-12345679'),
                    ]
                )
            )
        ]
    )]
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

    #[OAT\Delete(
        path: '/api/v1/product/{id}',
        operationId: 'productdelete',
        summary: 'Produkt löschen',
        tags: ['Products'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Produkt löschen',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Produkt gelöscht'),
                    ]
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Produkt nicht gefunden',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Produkt nicht gefunden'),
                    ]
                )
            )
        ]
    )]
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