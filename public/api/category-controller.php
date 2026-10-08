<?php

namespace App;

use mysqli;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController
{
    public function __construct(private mysqli $db)
    {
    }

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

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $stmt = $this->db->prepare("SELECT * FROM category WHERE id = ?");
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

    public function patch(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $data = json_decode($request->getBody()->getContents(), true);

        $active = $data["active"];
        $name = $data["name"];

        $stmt = $this->db->prepare("UPDATE category SET active = ?, name = ? WHERE id = ?");
        $stmt->bind_param("isi", $active, $name, $id);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Kategorie aktualisiert"]));

        return $response->withHeader("Content-Type", "application/json");
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args["id"];

        $stmt = $this->db->prepare("DELETE FROM category WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $response->getBody()->write(json_encode(["message" => "Kategorie gelöscht"]));

        return $response->withHeader("Content-Type", "application/json");
    }
}