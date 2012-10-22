<?php

namespace Ace\UtilitiesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ace\UtilitiesBundle\Handler\DefaultHandler;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends Controller
{
	public function newprojectAction()
	{

		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();

		$project_name = trim(basename(stripslashes($this->getRequest()->request->get('project_name'))), ".\x00..\x20");

		if($project_name == '')
		{
			return $this->redirect($this->generateUrl('AceGenericBundle_list'));
		}

		$projectmanager = $this->get('projectmanager');
		$response = $projectmanager->createAction($user, $project_name, "")->getContent();
		$response=json_decode($response, true);
		if($response["success"])
		{
			$utilities = new DefaultHandler();
			$default_text = $utilities->default_text();
			$response2 = $projectmanager->createFileAction($response["id"], $project_name.".ino", $default_text);
			$response2=json_decode($response2, true);
			if($response2["success"])
			{
				return $this->redirect($this->generateUrl('AceGenericBundle_project',array('id' => $response["id"])));
			}
		}

		return $this->redirect($this->generateUrl('AceGenericBundle_list'));
	}

	public function deleteprojectAction($id)
	{

		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();

		$projectmanager = $this->get('projectmanager');
		$response = $projectmanager->deleteAction($id)->getContent();
		$response=json_decode($response, true);
		return $this->redirect($this->generateUrl('AceGenericBundle_list'));
	}

	public function getDescriptionAction($id)
	{
		$projectmanager = $this->get('projectmanager');
		$response = $projectmanager->getDescriptionAction($id)->getContent();
		$response=json_decode($response, true);
		if($response["success"])
			return new Response($response["response"]);
		else
			return new Response("");
	}

	public function setDescriptionAction($id)
	{

		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();
		$description = $this->getRequest()->request->get('data');

		$projectmanager = $this->get('projectmanager');
		$response = $projectmanager->setDescriptionAction($id, $description)->getContent();
		return new Response("hehe");
	}

	public function sidebarAction()
	{
		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user) {
			throw $this->createNotFoundException('No user found with id '.$name);
		}
		$projectmanager = $this->get('projectmanager');
		$files = $projectmanager->listAction($user->getID())->getContent();
		$files=json_decode($files, true);

		return $this->render('AceUtilitiesBundle:Default:sidebar.html.twig', array('files' => $files));
	}

	public function downloadAction($id)
	{
		$htmlcode = 200;
		$extension =".ino";
		$projectmanager = $this->get('projectmanager');

		$name = $projectmanager->getNameAction($id)->getContent();
		$name = json_decode($name, true);
		$name = $name["response"];

		$files = $projectmanager->listFilesAction($id)->getContent();
		$files = json_decode($files, true);

		if(isset($files[0]))
		{
			//TODO: We should support multi-file downloading as well
			$value = $files[0]["code"];
		}
		else
		{
			$value = "";
			$htmlcode = 404;
		}

		$headers = array('Content-Type'		=> 'application/octet-stream',
			'Content-Disposition' => 'attachment;filename="'.$name.$extension.'"');

		return new Response($value, $htmlcode, $headers);
	}

	public function getBinaryAction($id)
	{
		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();
		$flags = $this->getRequest()->request->get('data');
		$flags = serialize(json_decode($flags, true));

		$projectmanager = $this->get('projectmanager');
		$bin = $projectmanager->getBinaryAction($id, $flags)->getContent();

		//TODO: This is stupid and needs to be changed, but it's ok for now
		$bin = json_decode($bin, true);
		$bin = json_decode($bin, true);
		$bin["binary"] = $bin["binary"]["binary"];
		$bin = json_encode($bin);

		return new Response($bin);
	}

	public function saveCodeAction($id)
	{

		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();
		$files = $this->getRequest()->request->get('data');
		$files = json_decode($files, true);

		$projectmanager = $this->get('projectmanager');
		foreach($files as $key => $file)
		{
			$response = $projectmanager->setFileAction($id, $key, htmlspecialchars_decode($file))->getContent();
			$response = json_decode($response, true);
			if($response["success"] ==  false)
				return new Response(json_encode($response));
		}
		return new Response(json_encode(array("success"=>true)));
	}

	public function createFileAction($id)
	{
		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();
		$data = $this->getRequest()->request->get('data');
		$data = json_decode($data, true);

		$projectmanager = $this->get('projectmanager');
		$response = $projectmanager->createFileAction($id, $data["filename"], "");
		$response = json_decode($response, true);
		if($response["success"] ==  false)
			return new Response(json_encode($response));
		return new Response(json_encode(array("success"=>true)));
	}

	public function deleteFileAction($id)
	{
		$name = $this->container->get('security.context')->getToken()->getUser()->getUsername();
		$user = $this->getDoctrine()->getRepository('AceExperimentalUserBundle:ExperimentalUser')->findOneByUsername($name);

		if (!$user)
		{
			throw $this->createNotFoundException('No user found with id '.$name);
		}

		$user = $user->getID();
		$data = $this->getRequest()->request->get('data');
		$data = json_decode($data, true);

		$projectmanager = $this->get('projectmanager');
		$response = $projectmanager->deleteFileAction($id, $data["filename"]);
		$response = json_decode($response, true);
		if($response["success"] ==  false)
			return new Response(json_encode($response));
		return new Response(json_encode(array("success"=>true)));
	}

	public function compileAction()
	{
		$response = new Response('404 Not Found!', 404, array('content-type' => 'text/plain'));

			$id = $this->getRequest()->request->get('project_id');
			$buildflags = array("mcu" => "atmega328p", "f_cpu" => "16000000L", "core" => "arduino", "variant" => "standard");

			if($id)
			{
				$projectmanager = $this->get('projectmanager');

				$files = $projectmanager->listFilesAction($id)->getContent();
				$files = json_decode($files, true);
				foreach($files as $key => $file)
				{
					$files[$key]["content"] = $file["code"];
					unset($files[$key]["code"]);
				}

				$value = array("files" => $files, "format" => "binary", "build" => $buildflags);
				$value = json_encode($value);
				$data = "ERROR";

				$utilities = $this->get('utilities');
				$data = $utilities->json_request($this->container->getParameter('compiler'), $value);
				$json_data = json_decode($data, true);

				if($json_data['success'])
				{
					$files = $projectmanager->setBinaryAction($id, serialize($buildflags), $json_data['output'])->getContent();

					unset($json_data['output']);
					$data = json_encode($json_data);
				}
				$response->setContent($data);
				$response->setStatusCode(200);
				$response->headers->set('Content-Type', 'text/html');
			}

		return $response;
	}

}
