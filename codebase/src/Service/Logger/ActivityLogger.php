<?php

namespace App\Service\Logger;

use App\Entity\ActivityLogs;
use App\Repository\ActivityLogsRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

class ActivityLogger
{

    public function __construct(private ActivityLogsRepository $activityLogsRepository,
                                private Environment $twig,
                                private TokenStorageInterface $tokenStorage)
    {
    }

    public function create($object, string $templateView){
        $user = $this->tokenStorage->getToken()->getUser();
        $activityLogs = new ActivityLogs();
        $activityLogs->setType("create");
        $activityLogs->setEntity($object->getId());
        $activityLogs->setSource($object::class);
        $activityLogs->setUser($user);
        $html = $this->twig->render($templateView, [
            "user" => $user,
            "data" => $object
        ]);
        $activityLogs->setHtmlContent($html);
        $activityLogs->setCreatedAt(new \DateTime('now'));
        $this->activityLogsRepository->save($activityLogs, true);
    }

    public function update($object, string $templateView){
        $user = $this->tokenStorage->getToken()?->getUser();
        $activityLogs = new ActivityLogs();
        $activityLogs->setType("update");
        $activityLogs->setEntity($object->getId());
        $activityLogs->setSource($object::class);
        $activityLogs->setUser($user);
        $html = $this->twig->render($templateView, [
            "user" => $user,
            "data" => $object
        ]);
        $activityLogs->setHtmlContent($html);
        $activityLogs->setCreatedAt(new \DateTime('now'));
        $this->activityLogsRepository->save($activityLogs, true);
    }


    public function delete($object, string $templateView){
        $user = $this->tokenStorage->getToken()->getUser();
        $activityLogs = new ActivityLogs();
        $activityLogs->setType("delete");
        $activityLogs->setEntity($object->getId());
        $activityLogs->setSource($object::class);
        $activityLogs->setUser($user);
        $html = $this->twig->render($templateView, [
            "user" => $user,
            "data" => $object
        ]);
        $activityLogs->setHtmlContent($html);
        $activityLogs->setCreatedAt(new \DateTime('now'));
        $this->activityLogsRepository->save($activityLogs, true);
    }

}
