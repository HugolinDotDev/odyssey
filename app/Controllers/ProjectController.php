<?php
/**
 * Controller for project pages/actions
 * 
 * @author Hugolin Mariette
 */

namespace App\Controllers;

use App\Entities\AdviceEntity;
use App\Entities\AssociateEntity;
use App\Entities\FeedbackEntity;
use App\Entities\MessageEntity;
use App\Entities\ProjectEntity;

class ProjectController extends AppController {

    public function new() {
        $this->redirectIfUserNotAuth('/user/login');
        echo $this->container->get('twig')->render('/pages/project/new.html.twig');
    }

    public function create(string $name, string $description) {
        $this->redirectIfUserNotAuth('/user/login');

        // At least one field is empty
        if (strlen($name) == 0 || strlen($description) == 0) {
            echo $this->container->get('twig')->render('/pages/project/new.html.twig', [
                'error' => "Au moins l'un des champs est vide."
            ]);
        }
        // Everything is okay
        else {
            $project_entity = new ProjectEntity;
            $projectId = $project_entity->create($name, $description, $this->sessionGet('userId'));

            $associate_entity = new AssociateEntity;
            $res = $associate_entity->add($this->sessionGet('userId'), $projectId);
            
            echo $this->container->get('twig')->render('/pages/project/success-create.html.twig', [
                'projectId' => $projectId
            ]);
        }
    }

    public function list() {
        $this->redirectIfUserNotAuth('/user/login');
        $project_entity = new ProjectEntity;
        $projects = $project_entity->list($this->sessionGet('userId'));

        if (!is_null($projects)) {
            foreach($projects as &$project) {
                $nb_associates = $project_entity->nbAssociates($project['id_projet']);
                $project['nb_collaborateurs'] = $nb_associates['nb_collaborateurs'];
            }
        }
        echo $this->container->get('twig')->render('/pages/project/list.html.twig', [
            'projects' => $projects
        ]);
    }

    public function view(int $projectId) {
        $this->redirectIfUserNotAuth('/user/login');
        if (!$this->isUserAssociatedTo($projectId))
            $this->redirect('/project/list');

        $project_entity = new ProjectEntity;
        $associate_entity = new AssociateEntity;
        $message_entity = new MessageEntity;
        $advice_entity = new AdviceEntity;
        $feedback_entity = new FeedbackEntity;

        $project = $project_entity->get($projectId);
        $associates = $associate_entity->getForProject($projectId);
        $messages = $message_entity->getForProject($projectId);
        $advice = $advice_entity->getRandomAdvice();
        $feedbacks = $feedback_entity->getForProject($projectId);

        echo $this->container->get('twig')->render('/pages/project/view.html.twig', [
            'project' => $project,
            'associates' => $associates,
            'messages' => $messages,
            'advice' => $advice,
            'feedbacks' => $feedbacks
        ]);
    }

    public function delete(int $projectId) {
        $this->redirectIfUserNotAuth('/user/login');
        if(!$this->isUserLeaderOf($projectId))
            $this->redirect('/project/list');

        $project_entity = new ProjectEntity;
        $project_entity->delete($projectId);
        $this->redirect('/project/list');
    }

}