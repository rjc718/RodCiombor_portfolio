'use strict';

var app = angular.module("myApp", ['ngRoute']);

angular.module('myApp').config(function($routeProvider){
    $routeProvider.
    when('/prog-book',{controller: 'progController', templateUrl: 'partials/prog-book.html'}).
    when('/cook-book',{controller: 'cookController', templateUrl: 'partials/cook-book.html'}).
    when('/finance-book',{controller:'financeController',templateUrl: 'partials/finance-book.html'}).
    otherwise({redirectTo:'/prog-book'});

});
