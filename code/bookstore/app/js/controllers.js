'use strict';

app.controller("progController",['$scope',function($scope){
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

app.controller("cookController",['$scope',function($scope){
    $scope.products =
    [
        {
            name: 'Bacon & Butter: The Ultimate Ketogenic Diet Cookbook',
            author: 'Celby Richoux',
            publisher: 'Rockridge Press',
            price: '11.50',
            cover: 'img/bacon-butter.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'Cravings: Recipes for All the Food You Want to Eat',
            author: 'Chrissy Teigen',
            publisher: 'Clarkson Potter Publishers',
            price: '17.99',
            cover: 'img/cravings.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'Every Grain of Rice: Simple Chinese Home Cooking',
            author: 'Fuchsia Dunlop',
            publisher: 'W. W. Norton & Company',
            price: '24.02',
            cover: 'img/every-grain-rice.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'The Food Lab: Better Home Cooking Through Science',
            author: 'J. Kenji Lopez-Alt',
            publisher: 'W. W. Norton & Company',
            price: '27.47',
            cover: 'img/food-lab.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'James Beard\'s Menus for Entertaining',
            author: 'James Beard',
            publisher: 'Open Road Media',
            price: '5.29',
            cover: 'img/james-beard-menu.jpg',
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

app.controller("financeController",['$scope',function($scope){
    $scope.products =
    [
        {
            name: 'The Total Money Makeover: A Proven Plan for Financial Fitness',
            author: 'Dave Ramsey',
            publisher: 'Thomas Nelson Publishing',
            price: '15.99',
            cover: 'img/money-makeover.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'Rich Dad Poor Dad',
            author: 'Robert T. Kiyosaki',
            publisher: 'Warner Books',
            price: '15.08',
            cover: 'img/rich-poor.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'Think & Grow Rich',
            author: 'Napoleon Hill',
            publisher: 'Dauphin Publications',
            price: '6.95',
            cover: 'img/think-grow-rich.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'The Wealthy Barber: The Common Sense Guide to Successful Financial Planning',
            author: 'David Chilton',
            publisher: 'Stoddart Publishing',
            price: '7.29',
            cover: 'img/wealthy-barber.jpg',
            likes: 0,
            dislikes: 0  
        },
        {
            name: 'The Money Book for the Young, Fabulous & Broke',
            author: 'Suze Orman',
            publisher: 'Riverhead Books',
            price: '12.99',
            cover: 'img/yfb.jpg',
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