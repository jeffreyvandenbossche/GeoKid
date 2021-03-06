<?php

namespace skeleton\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class PlaygroundController implements ControllerProviderInterface {
	
	/**
	 * Returns routes to connect to the given application.
	 * @param Application $app 			An Application instance
	 * @return ControllerCollection 	A ControllerCollection instance
	 */
	public function connect(Application $app) 
	{
		// Create new ControllerCollection
		$controllers = $app['controllers_factory'];

		// Example routes
		$controllers
			->get('/', array($this, 'index'));

		$controllers
			->get('/{id}/tasks', array($this, 'detailtasks'))
			->assert('id', '\d+');

		$controllers
			->get('/{id}/', array($this, 'detail'))
			->assert('id', '\d+');

		$controllers
			->match('/{id}/visit', array($this, 'visit'))
			->method('GET|POST')
			->assert('id', '\d+');

		$controllers
			->match('/visitedplaygrounds', array($this, 'visitedplaygrounds'))
			->method('GET|POST')
			->assert('id', '\d+');

		$controllers
			->match('/{id}/favorite', array($this, 'favorite'))
			->method('GET|POST')
			->assert('id', '\d+');

		$controllers
			->match('/{id}/undofavorite', array($this, 'undofavorite'))
			->method('GET|POST')
			->assert('id', '\d+');

		return $controllers;
	}

	/**
	 * Returns the action's response
	 * @param  Application 	$app 	An Application instance
	 * @return string           	A html string rendered by twig
	 */
	public function index(Application $app) 
	{
		$playgrounds = $app['db.playgrounds']->findAllPlaygrounds();
		// var_dump($playgrounds);
		return new JsonResponse($playgrounds);
	}

	public function detailtasks(Application $app, $id) 
	{
		$playground = $app['db.playgrounds']->findSpecficPlaygroundWithTasks($id);
		// var_dump($playgrounds);
		$di = new \DirectoryIterator($app['photoPlayground.base_path']);
		$photos = null;
		foreach ($di as $file) {
			if ($file->getExtension() == 'jpg') {
				$photos = array(
					'url' => $app['photoPlayground.base_url'] . '/' . $file,
					'title' => $file->getFileName()
				);
			}
		}
		$playground['photo'] = $photos;
		return new JsonResponse($playground);
	}

	public function detail(Application $app, $id) 
	{
		$playground = $app['db.playgrounds']->findSpecficPlayground($id);
		$di = new \DirectoryIterator($app['photoPlayground.base_path']);
		$photos = null;
		foreach ($di as $file) {
			if ($file->getExtension() == 'jpg') {
				$photos = array(
					'url' => $app['photoPlayground.base_url'] . '/' . $file,
					'title' => $file->getFileName()
				);
			}
		}
		$playground['photo'] = $photos;
		// var_dump($playgrounds);
		return new JsonResponse($playground);
	}

	public function visit(Application $app, $id, Request $request) 
	{
		$AuthKey = $request->headers->get('AuthKey');
		$masteracc = $app['db.masteraccounts']->findMasteraccountOnAuthKey($AuthKey);
		if($masteracc != null){
		
			$subaccountIds = $request->get('subaccountsId');

			// $data['subaccounts_MasterAccounts_Id'] = $masteraccId;
			$data['playgrounds_Id'] = $id;

			foreach ($subaccountIds as $value){
				$data['subaccounts_Id']= $value;
				$InDB = $app['db.playgrounds_has_subaccounts']-> findVisitedPlayground($id, $value);
				if(!$InDB)
					$playground = $app['db.playgrounds_has_subaccounts']->insert($data);

			}
			return new JsonResponse();
		}
		return new JsonResponse(false);

	}

	public function visitedplaygrounds(Application $app, Request $request) 
	{
		$AuthKey = $request->headers->get('AuthKey');
		$masteracc = $app['db.masteraccounts']->findMasteraccountOnAuthKey($AuthKey);
		if($masteracc != null){
			$subaccId = $request->get('subaccountsId');


			$playground = $app['db.playgrounds_has_subaccounts']->findallVisitedPlayground($subaccId);
			return new JsonResponse($playground);
		}
		return new JsonResponse(false);

	}

	public function favorite(Application $app, $id, Request $request) 
	{
		$masteraccId = $request->get('masteraccId');
		$data['Playgrounds_Id'] = $id;
		$data['MasterAccounts_Id'] = $masteraccId;

		$playground = $app['db.Favorite_Parks_MasterAccount']->findVisitedPlayground($id, $masteraccId);
		$playground['Favorite_playground'] = 1;
		$data = $app['db.Favorite_Parks_MasterAccount']->update($playground, array('MasterAccounts_Id' => $masteraccId, 'Playgrounds_Id' => $id ));
		return new JsonResponse($data);
	}

	public function undofavorite(Application $app, $id, Request $request) 
	{
		$masteraccId = $request->get('masteraccId');
		$data['Playgrounds_Id'] = $id;
		$data['MasterAccounts_Id'] = $masteraccId;

		$playground = $app['db.Favorite_Parks_MasterAccount']->findVisitedPlayground($id, $masteraccId);
		$playground['Favorite_playground'] = 0;
		$data = $app['db.Favorite_Parks_MasterAccount']->update($playground, array('MasterAccounts_Id' => $masteraccId, 'Playgrounds_Id' => $id ));
		return new JsonResponse($data);
	}
}