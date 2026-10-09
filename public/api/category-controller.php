<?php

namespace App;

use mysqli;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use OpenApi\Attributes as OAT;

class CategoryController
{
    public function __construct(private mysqli $db)
    {
    }

    #[OAT\Get(
        path: '/api/v1/categories',
        operationId: 'categorylist',
        summary: 'Alle Kategorien anzeigen',
        tags: ['Category'],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Liste aller Kategorien',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        type: 'object',
                        properties: [
                            new OAT\Property(property: 'category_id', type: 'string', example: '2'),
                            new OAT\Property(property: 'active', type: 'string', example: '1'),
                            new OAT\Property(property: 'name', type: 'string', example: 'Autos'),
                        ]
                    )
                )
            )
        ]
    )]
    
    public function list(Request $request, Response $response): Response
    {
        $result = $this->db->query("SELECT * FROM category");

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        $response->getBody()->write(json_encode($categories));

        return $response->withHeader("Content-Type", "application/json");
    }

    #[OAT\Get(
        path: '/api/v1/category/{id}',
        operationId: 'categorysingle',
        summary: 'Kategorie durch ID anzeigen',
        tags: ['Category'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Kategorie info',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'category_id', type: 'integer', example: 4),
                        new OAT\Property(property: 'active', type: 'integer', example: 0),
                        new OAT\Property(property: 'name', type: 'string', example: 'Parfum'),
                    ]
                )
            ),
            new OAT\Response(
                response: 404,
                description:'Kategorie nicht gefunden',
                content: new OAT\JsonContent(
                    type:'object',
                    properties: [
                        new OAT\Property(property: 'error', type: 'intiger', example:'Kategorie nicht gefunden'),
                    ]
                )
            )
        ]
    )]
    public function get(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $stmt = $this->db->prepare("SELECT * FROM category WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $category = $result->fetch_assoc();

        if (!$category) {
            $response->getBody()->write(json_encode(["error" => "Kategorie nicht gefunden"]));

            return $response
                ->withStatus(404)
                ->withHeader("Content-Type", "application/json");
        }

        $response->getBody()->write(json_encode($category));

        return $response->withHeader("Content-Type", "application/json");
    }

    #[OAT\Post(
        path: '/api/v1/category',
        operationId: 'categorycreate',
        summary: 'Kategorie erstellen',
        tags: ['Category'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                type: 'object',
                properties: [
                    new OAT\Property(property: 'active', type: 'integer', example: 1),
                    new OAT\Property(property: 'name', type: 'string', example: 'Gugu'),
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Kategorie erstellt',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Kategorie erstellt'),
                        new OAT\Property(property: 'id', type: 'integer', example: 5),
                    ]
                )
            ),
        ]
    )]
    public function post(Request $request, Response $response): Response
    {
        $data = json_decode($request->getBody()->getContents(), true);

        $active = $data["active"];
        $name = $data["name"];

        $stmt = $this->db->prepare("INSERT INTO category (active, name) VALUES (?, ?)");
        $stmt->bind_param("is", $active, $name);
        $stmt->execute();

        $response->getBody()->write(json_encode([
            "message" => "Kategorie erstellt",
            "id" => $this->db->insert_id
        ]));

        return $response
            ->withStatus(201)
            ->withHeader("Content-Type", "application/json");
    }

    #[OAT\Patch(
        path: '/api/v1/category/{id}',
        operationId: 'categorypatch',
        summary: 'Kategorie aktualisieren',
        tags: ['Category'],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                type: 'object',
                properties: [
                    new OAT\Property(property: 'active', type: 'integer', example: 1),
                    new OAT\Property(property: 'name', type: 'string', example: 'Gugu'),
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Kategorie erstellt',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Kategorie aktualisiert'),
                    ]
                )
            ),
        ]
    )]
    public function patch(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $data = json_decode($request->getBody()->getContents(), true);

        $active = $data["active"];
        $name = $data["name"];

        $stmt = $this->db->prepare("UPDATE category SET active = ?, name = ? WHERE category_id = ?");
        $stmt->bind_param("isi", $active, $name, $id);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Kategorie aktualisiert"]));

        return $response->withHeader("Content-Type", "application/json");
    }

    #[OAT\Delete(
        path: '/api/v1/category/{id}',
        operationId: 'categorydelete',
        summary: 'Kategorie löschen',
        tags: ['Category'],
        parameters: [
            new OAT\Parameter(name: 'id', in: 'path', required: true, schema: new OAT\Schema(type: 'integer')),
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Kategorie löschen',
                content: new OAT\JsonContent(
                    type: 'object',
                    properties: [
                        new OAT\Property(property: 'message', type: 'string', example: 'Kategorie gelöscht'),
                    ]
                )
            ),
        ]
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $stmt = $this->db->prepare("DELETE FROM category WHERE category_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Kategorie gelöscht"]));

        return $response->withHeader("Content-Type", "application/json");
    }
}