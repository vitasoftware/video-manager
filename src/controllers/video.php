<?php

use Symfony\Component\HttpFoundation\Request;
use \Vimeo\VimeoAPIException;

$videoController = $app['controllers_factory'];

$videoController->get('/', function () use($app) {
    $videos = $app['service.video']->getAll();
    return $app['twig']->render('videos/index.twig', array('videos' => $videos));
})->bind('videos');

$videoController->get('/add', function() use($app) {

    $form = $app['vita.util']->getForm('videoUpload');

    return $app['twig']->render('videos/add.twig', array('form' => $form->createView() ));
})->bind('videos-add');

$videoController->post('/add', function(Request $request) use($app) {

    $type = 'error';

    try{
        $form = $app['vita.util']->getForm('videoUpload');

        $form->bind($request);

        if( !$form->isValid() ) {
            throw new \InvalidArgumentException($form->getErrorsAsString());
        }

        extract($form->getData());

        $app['service.video']->uploadVideo($title, $description, $file);

        $type = 'success';
        $short = 'Ok';
        $ext = 'Upload realizado com sucesso.';
    }catch(VimeoAPIException $e){
        $short = 'Vimeo';
        $ext = $e->getMessage();
    }catch(\Exception $e){
        $short = 'Desconhecido';
        $ext = $e->getMessage();
    }

    $app['vita.util']->addFlashMessage($short, $ext, $type);

    return $app->redirect( $app['url_generator']->generate('videos') );
})->bind('videos-add-save');

$videoController->get('/watch/{video_id}', function($video_id) use($app) {

    $dadosVideo = $app['service.video']->getVideoData($video_id);

    return $app['twig']->render('videos/watch.twig', array('video' => $dadosVideo ));
})->bind('videos-watch');

return $videoController;