<?php

namespace JarenalBundle\Controller;

use JarenalBundle\Entity\League;
use JarenalBundle\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LeaguesController extends Controller implements JWTController
{
    /**
     * @Route("/leagues/{id}", methods={"GET"})
     * @param string $id
     * @return string
     */
    public function getAction($id)
    {
        $response = ["message" => ""];
        $http_status = 200;

        try {
            $league = $this->getDoctrine()->getRepository(League::class)->find($id);

            if (!$league) {
                throw new \Exception("The given league doesn't exist", 404);
            }

            $serializer = $this->get("serializer");
            $response = $serializer->normalize($league, null, [
                'enable_max_depth' => true,
                'groups' => ['group1']
            ]);
        } catch (\Exception $ex) {
            switch ($ex->getCode()) {
                case 404:
                    $response['message'] = $ex->getMessage();
                    $http_status = $ex->getCode();
                    break;
                default:
                    $response['message'] = "Something was wrong";
                    $http_status = 500;
                    break;
            }
        }

        return $this->json($response, $http_status);
    }

    /**
     * @Route("/leagues/{id}", methods={"DELETE"})
     * @param string $id
     * @return string
     */
    public function deleteAction($id)
    {
        $response = [];
        $http_status = 204;

        try {
            $league = $this->getDoctrine()->getRepository(League::class)->find($id);

            if (!$league) {
                throw new \Exception("The given league doesn't exist", 404);
            }

            $em = $this->getDoctrine()->getManager();
            $em->remove($league);
            $em->flush();
        } catch (\Exception $ex) {
            switch ($ex->getCode()) {
                case 404:
                    $response['message'] = $ex->getMessage();
                    $http_status = $ex->getCode();
                    break;
                default:
                    $response['message'] = "Something was wrong";
                    $http_status = 500;
                    break;
            }
        }

        return $this->json($response, $http_status);
    }

    /**
     * @Route("/leagues/{league_id}/teams", methods={"POST"})
     * @param Request $request
     * @param string $league_id
     * @return string
     */
    public function postAction(Request $request, $league_id)
    {
        $response = ["message" => "Team created successfully"];
        $http_status = 201;

        try {
            $name = $request->request->get("name");
            if (!$name) {
                throw new \Exception("name is required", 400);
            }

            $strip = $request->request->get("strip");
            if (!is_array($strip) || !$strip) {
                throw new \Exception("strip is required", 400);
            }

            $em = $this->getDoctrine()->getManager();
            $league = $em->getRepository(League::class)->find($league_id);

            if (!$league) {
                throw new \Exception("The given league doesn't exist", 404);
            }

            $team = new Team();
            $team->setName($name);
            $team->setStrip($strip);
            $team->setLeague($league);
            $em->persist($team);
            $em->flush();
        } catch (\Exception $ex) {
            switch ($ex->getCode()) {
                case 400:
                case 404:
                    $response['message'] = $ex->getMessage();
                    $http_status = $ex->getCode();
                    break;
                default:
                    $response['message'] = "Something was wrong";
                    $http_status = 500;
                    break;
            }
        }

        return $this->json($response, $http_status);
    }

    /**
     * @Route("/leagues/{league_id}/teams/{team_id}", methods={"PUT"})
     * @param Request $request
     * @param string $league_id
     * @param string $team_id
     * @return string
     */
    public function putAction(Request $request, $league_id, $team_id)
    {
        $response = [];
        $http_status = 204;

        try {
            $em = $this->getDoctrine()->getManager();

            $name = $request->request->get("name");
            if (!$name) {
                throw new \Exception("name is required", 400);
            }

            $strip = $request->request->get("strip");
            if (!is_array($strip) || !$strip) {
                throw new \Exception("strip is required", 400);
            }

            $league = $em->getRepository(League::class)->find($league_id);
            if (!$league) {
                throw new \Exception("The given league doesn't exist", 404);
            }

            $team = $em->getRepository(Team::class)->find($team_id);
            if (!$team) {
                throw new \Exception("The given team doesn't exist", 404);
            }

            $team->setName($name);
            $team->setStrip($strip);
            $team->setLeague($league);
            $em->persist($team);
            $em->flush();
        } catch (\Exception $ex) {
            switch ($ex->getCode()) {
                case 400:
                case 404:
                    $response['message'] = $ex->getMessage();
                    $http_status = $ex->getCode();
                    break;
                default:
                    $response['message'] = "Something was wrong";
                    $http_status = 500;
                    break;
            }
        }

        return $this->json($response, $http_status);
    }
}
