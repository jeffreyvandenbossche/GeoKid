<?php

namespace skeleton\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class SubAccountController implements ControllerProviderInterface {
	
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
			->get('/', array($this, 'getSubAccounts'))
			->assert('masteraccId','\d+');

		$controllers
			->post('/create', array($this, 'createSubAccount'))
			->assert('masteraccId','\d+');

		$controllers
			->get('/{id}', array($this, 'getSubAccount'))
			->assert('masteraccId','\d+')
			->assert('id','\d+');

		$controllers
			->post('/{id}/change', array($this, 'changeSubAccount'))
			->assert('masteraccId','\d+')
			->assert('id','\d+');

		$controllers
			->match('/{id}/delete', array($this, 'deletesubAccount'))
			->method('GET|POST')
			->assert('masteraccId','\d+')
			->assert('id','\d+');

		$controllers
			->post('/uploadpic', array($this, 'uploadpic'))
			->assert('masteraccId','\d+')
			->assert('id','\d+');

		return $controllers;
	}

	public function getSubAccounts(Application $app, Request $request) 
	{
		$AuthKey = $request->headers->get('AuthKey');

		$masteraccId = $request->get('masteraccId');
		$user = $app['db.masteraccounts']->findMasteraccountOnId($masteraccId, $AuthKey);
		if($user != false){
			$subaccounts = $app['db.subaccounts']->findAllSubAccounts($masteraccId);
			$subaccounts2 = [];
			foreach ($subaccounts as $subaccount) {
				$photos= null;
				$di = new \DirectoryIterator($app['photoSubaccount.base_path']);
				foreach ($di as $file) {

					if ($file->getExtension() == 'jpg') {
						if($subaccount['Id'] == current(explode('-', $file->getFileName()))){
							$photos = array(
								'url' => $app['photoSubaccount.base_url'] . '/' . $file,
								'title' => $file->getFileName()
							);
							
						}
					}
				};
				$subaccount['photo']= $photos;
				array_push($subaccounts2, $subaccount);

			}
			return new JsonResponse($subaccounts2);
		}
		return new JsonResponse(false);
		
	}

	public function createSubAccount(Request $request, Application $app) 
	{
		$name = $request->get('name');

		$AuthKey = $request->headers->get('AuthKey');

		$masteraccId = $request->get('masteraccId');
		$user = $app['db.masteraccounts']->findMasteraccountOnId($masteraccId, $AuthKey);
		if($user != false){
			$data['Name']= $name;
			$data['MasterAccounts_Id']= $masteraccId;
			$subaccount = $app['db.subaccounts']->insert($data);

			return new JsonResponse($subaccount);

		}
		return new JsonResponse(false);
		
	}


	public function uploadpic(Request $request, Application $app) 
	{
		$subaccId = $_POST['subaccId'];

		if(isset($_FILES['photo'])){
			//Look if image exist
			$di = new \DirectoryIterator($app['photoSubaccount.base_urlServer']);
			foreach ($di as $file) {
				if ($file->getExtension() == 'jpg') {
					if($subaccId == current(explode('-', $file->getFileName()))){		
						
							unlink($app['photoSubaccount.base_urlServer'] . '/' . $file);
							
					}
				}

			};

			// exit();
		 	$date = (new\ DateTime('now', new\ DateTimeZone('UTC'))) -> format('dmY_His');
		    $temp = explode(".", $_FILES["photo"]["name"]);
			$filename = $subaccId.'-'.$date . '.' . end($temp);
			// var_dump($filename);
		    // $filename = $_FILES['photo']['name'];

		    move_uploaded_file($_FILES['photo']['tmp_name'], 'assets/public/img/subaccounts/'.$filename);
		 
		   
		}else{
		    echo "No File!";
		}
		return new JsonResponse(null);
		
	}

	public function getSubAccount(Application $app, Request $request) 
	{
		$masteraccId = $request->get('masteraccId');
		$subaccId = $request->get('id');
		$AuthKey = $request->headers->get('AuthKey');
		$masteracc = $app['db.masteraccounts']->findMasteraccountOnAuthKey($AuthKey);
		if($masteracc != null){
			$subaccount = $app['db.subaccounts']->findSubAccount($subaccId);
			if($subaccount != false){
				$photos = null;
				$di = new \DirectoryIterator($app['photoSubaccount.base_path']);
				foreach ($di as $file) {

					if ($file->getExtension() == 'jpg') {
						if($subaccId == current(explode('-', $file->getFileName()))){		
							$photos = array(
								'url' => $app['photoSubaccount.base_url'] . '/' . $file,
								'title' => $file->getFileName()
							);
							
						}
					}
				};
				$subaccount['photo']= $photos;
				
				return new JsonResponse($subaccount);
			}
			return new JsonResponse(null);
				
		}


		

		return new JsonResponse(false);
		
	}

	public function changeSubAccount(Application $app, Request $request) 
	{
		$AuthKey = $request->headers->get('AuthKey');
		$masteracc = $app['db.masteraccounts']->findMasteraccountOnAuthKey($AuthKey);

		if($masteracc != null){
			$masteraccId = $request->get('masteraccId');
			$subaccId = $request->get('id');
			$name = $request->get('name');
						

			$subaccount = $app['db.subaccounts']->findSubAccount($subaccId);
		
			if($subaccount != null){
				$subaccount['Name'] = $name;

				$data = $app['db.subaccounts']->update($subaccount, array('id' => $subaccId));
				return new JsonResponse($data);
			}
			return new JsonResponse(null);

		}
		return new JsonResponse(false);

		
	}

	public function deletesubAccount(Application $app, Request $request) 
	{
		$AuthKey = $request->headers->get('AuthKey');
		$masteracc = $app['db.masteraccounts']->findMasteraccountOnAuthKey($AuthKey);
		if($masteracc != null){

			$masteraccId = $request->get('masteraccId');
			$subaccId = $request->get('id');

			$subaccount = $app['db.subaccounts']->findSubAccount($subaccId);

			if($subaccount != null){
				$data = $app['db.playgrounds_has_subaccounts']->delete( array('subaccounts_Id' => $subaccId));
				$data = $app['db.tasks']->delete( array('SubAccounts_Id' => $subaccId));
				$data = $app['db.achievements_has_subaccounts']->delete( array('SubAccounts_Id' => $subaccId));
				$data = $app['db.subaccounts']->delete( array('id' => $subaccId));
				return new JsonResponse($data);
			}

			return new JsonResponse(null);

		}
		return new JsonResponse(false);
	}
}