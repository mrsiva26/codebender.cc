<?php

namespace Ace\GenericBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class EditorController extends Controller
{		
	public function editAction($id)
	{
		if (!$this->get('security.context')->isGranted('ROLE_USER'))
		{
			return $this->forward('AceGenericBundle:Default:project', array("id"=> $id));
		}

		$user = json_decode($this->get('usercontroller')->getCurrentUserAction()->getContent(), true);

		$projectmanager = $this->get('projectmanager');
		$owner = $projectmanager->getOwnerAction($id)->getContent();
		$owner = json_decode($owner, true);
		$owner = $owner["response"];

		if($owner["id"] != $user["id"])
		{
			return $this->forward('AceGenericBundle:Default:project', array("id"=> $id));
		}

		$name = $projectmanager->getNameAction($id)->getContent();
		$name = json_decode($name, true);
		$name = $name["response"];

		$files = $projectmanager->listFilesAction($id)->getContent();
		$files = json_decode($files, true);
		$files = $files["list"];

		foreach($files as $key=>$file)
		{
			$files[$key]["code"] = htmlspecialchars($file["code"]);
		}

		$boardcontroller = $this->get('boardcontroller');
		$boards = $boardcontroller->listAction()->getContent();

		return $this->render('AceGenericBundle:Editor:editor.html.twig', array('project_id' => $id, 'project_name' => $name, 'files' => $files, 'boards' => $boards));
	}

	public function embeddedCompilerAction($id)
	{

		$projectmanager = $this->get('projectmanager');

		$files = $projectmanager->listFilesAction($id)->getContent();
		$files = json_decode($files, true);
		$files = $files["list"];

		foreach ($files as $key => $file)
		{
			$files[$key]["code"] = htmlspecialchars($file["code"]);
		}

		$boardcontroller = $this->get('boardcontroller');
		$boards = $boardcontroller->listAction()->getContent();

		return $this->render('AceGenericBundle:Editor:compiler_embeddable.html.twig', array('files' => $files, 'boards' => $boards));
	}
}
