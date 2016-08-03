'use strict';

app.controller("ProductController",['$scope',function($scope){
    $scope.products =
    [
        {
            name: 'Angular JS Novice To Ninja',
            author: 'Sandeep Panda',
            publisher: 'Sitepoint Publishing',
            price: '15.99',
            cover: 'img/angular_novice_to_ninja.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'HTML & CSS Design and Build Websites',
            author: 'Jon Duckett',
            publisher: 'John Wiley and Sons',
            price: '15.99',
            cover: 'img/html_css.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'Introduction to Java Programming, 10th Edition',
            author: 'Y. Daniel Liang',
            publisher: 'Pearson',
            price: '65.10',
            cover: 'img/java.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'Murach\'s PHP and MySQL, 2nd Edition',
            author: 'Joel Murach & Ray Harris',
            publisher: 'Murach\'s Books',
            price: '43.60',
            cover: 'img/murach_php.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'RESTful Web Services Cookbook',
            author: 'Subbu Allamaraju',
            publisher: 'O\'Reilly Media, Inc.',
            price: '19.48',
            cover: 'img/restful.jpg',
            likes: 0,
            dislikes: 0  
        }
                       
    ];
    
    $scope.plusOne = function(index){
        $scope.products[index].likes += 1;    
    };
    
    $scope.minusOne = function(index){
        $scope.products[index].dislikes += 1;    
    };
    
}]);