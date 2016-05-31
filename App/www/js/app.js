var app = angular.module('GeoKidApp', ['ionic','ngCordova','GeoKidApp.services', 'GeoKidApp.controllers', 'GeoKidApp.filters']);
var baseUri = 'https://api.jeffreyvdb.be';
// var baseUri = 'http://localhost:8000';


// === Basic Setup
app.run(
    function($ionicPlatform) {
      $ionicPlatform.ready(function() {
        if(window.cordova && window.cordova.plugins.Keyboard) {
          cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
          cordova.plugins.Keyboard.disableScroll(true);
        }
        if(window.StatusBar) {
          StatusBar.styleDefault();
        }
      })
    }
);

// Angular UIRouter manage the navigation between the pages/views of the app
app.config(
    function($stateProvider, $urlRouterProvider) {
        $stateProvider
            .state('login', {
              url: '/login',
              templateUrl: 'templates/login.html',
              controller: 'LoginCtrl'
            })
            .state('register', {
              url: '/register',
              templateUrl: 'templates/register.html',
              controller: 'RegisterCtrl'
            })
            .state('mapoverview', {
                url: '/mapoverview',
                templateUrl: 'templates/mapoverview.html',
                controller: 'MapOverviewCtrl'
            })

            .state('detailsub', {
                url: '/detail/:userId',
                templateUrl: 'templates/detail.html',
                controller: 'DetailSubaccCtrl'
            })

            .state('createsub', {
              url: '/subaccounts/create',
              templateUrl: 'templates/create.html',
              controller: 'CreateSubCtrl'
            })

            .state('editsub', {
              url: '/account/:userId/edit',
              templateUrl: 'templates/create.html',
              controller: 'EditsubCtrl'
            })

            .state('deletesub', {
              url: '/account/:userId/delete',
              controller: 'DeletesubCtrl'
            })

            .state('subaccounts', {
                'url': '/subaccounts',
                'templateUrl': 'templates/subacc.html',
                'controller': 'SubAccCtrl'
            })
            ;

        // if none of the above states are matched, use this as the fallback
        // $urlRouterProvider.otherwise('/subaccounts/create');
        $urlRouterProvider.otherwise('/login');
    }
);

