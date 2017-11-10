/**
* @author Mücahid Dayan
* 
*/

const headers = {
    'Content-Type':'application/json'
}


/**
* 
* @param {WP_Post} recipe
* title required
* @returns {id:number} of the just created recipe
*/
let createRecipe = (recipe)=>{
    // if(!recipe.hasOwnProperty('title') || recipe.title == ''){
    if(!recipe.title){
        console.warn('Title is required and it can not be emtpy');
        return;
    }
    return fetch(`${location.origin}/wp-json/wp/v2/recipes`,{
        method:'post',
        credentials: 'include',
        headers:{
            'X-WP-Nonce':veganRezept.nonce,
            'Content-Type':'application/json'
        },
        body:JSON.stringify(recipe),
    })
    .then(e=>e.json())
    .catch(err=>console.log(err));
}

/**
* 
* @param {WP_Post} recipe 
*/
let editRecipe = (recipe)=>{
    if(!recipe.id || recipe.id == 0){
        console.warn('ID is required and it can not be 0');
        return;
    }
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${recipe.id}`,{
        method:'post',
        credentials: 'include',
        headers:{
            'X-WP-Nonce':veganRezept.nonce,
            'Content-Type':'application/json'
        },
        body:JSON.stringify(recipe),
    })
    .then(e=>e.json())
    .catch(err=>console.log(err));
}

/**
* deletes recipe with given id
* @param {number} id
* @returns {Promise}
*/
let deleteRecipe = (id)=>{
    if(!id || id == 0){
        console.warn('ID is required and it can not be 0');
        return;
    }
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${id}`,{
        method:'delete',
        credentials: 'include',
        headers:{
            'X-WP-Nonce':veganRezept.nonce,
            'Content-Type':'application/json'
        }
    })
    .then(e=>e.json())
    .catch(err=>console.log(err));
}

/**
* get all recipes
* @returns {Promise}
*/
let getRecipes = ()=>{
    return fetch(`${location.origin}/wp-json/wp/v2/recipes`,{
        headers:{
            'Content-Type':'application/json'
        }
    })
    .then(e=>e.json())
    .catch(err=>console.log(err));
}

/**
* 
* @param {number} id 
* @returns {Promise}
*/
let getRecipe = (id)=>{
    if(!id){
        console.warn('ID is required');
        return;
    }
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${id}`,{
        headers
    })
    .then(e=>e.json())
    .catch(err=>console.log(err));
}

let getRecipesFromMyBook = ()=>{    
    return fetch(`${location.href}wp-json/wp/v2/book?userID=${veganRezept.userID}`)
    .then(e=>e.json())
    .catch(err=>console.log(err))
}

let addToMyBook = (recipeID)=>{
    if(!recipeID){
        console.warn('Recipe ID can not be empty');
        return;
    }    
    fetch(`${location.href}wp-json/wp/v2/book/`,{
        method:'POST',
        headers,
        body:{
            recipeID,
            userID:veganRezept.userID
            }
    })
    .then(e=>e.json())
    .catch(err=>console.log(err))
}

/**
* 
* @param {number} start 
* @param {number} end 
* @param {number} range 
* @returns {array<number>}
*/
let createArray = (start=0,end=10,range=1)=>{
    var array = [];
    for(let i = start; i< end; i += range){
        array.push(i);
    }
    return array;
}

//################  ANGULARJS ########################
let app = angular.module('app',[]);

app.controller('mainController',['$scope',($scope)=>{
    $scope.title = "Rezepte";
}]);


// DIRECTIVES
app.directive('recipes',()=>{
    let link = (scope,el,attrs)=>{
        scope.recipes = JSON.parse(veganRezept.recipes);
        scope.defaultImg = `${veganRezept.pluginDirUrl}/img/salad.png`;
        console.log(scope.recipes);
    }
    
    let template = (el,attrs)=>{
        return `
        <div id="filter-wrapper">
        <input type="text" ng-model="search" placeholder="Suche nach Rezepten"/>
        </div>
        <div id="recipes-container">
        <div class="recipe-wrapper" ng-ig="recipes.length" ng-repeat="recipe in recipes |filter:search track by $index">
        <div class="recipe">
        <div class="recipe-img">
        <img ng-attr-title="{{recipe.img_url?recipe.post_title:'Icons made by Smashicons from CC 3.0 BY'}}" ng-src="{{recipe.img_url?recipe.img_url:defaultImg}}" alt="{{recipe.post_title}}"/>
        </div>
        <a target="_blank" rel="nofollow" href="{{recipe.guid}}">
        <span title="{{recipe.post_title}}" ng-bind="recipe.post_title |excerpt:2"></span>
        </a>
        </div>
        <h2 class="recipes-empty" ng-if="!recipes.length">Es gibt kein Rezept zu zeigen!</h2>
        </div>
        </div>
        `;
    }
    return {
        restrict : 'E',
        scope:{
            rInit : '=',
        },
        template:template,
        link:link
    };
});

app.filter('excerpt',()=>{
    return (input,lim,type='word')=>{
        let spl;
        if(type == 'word'){
            spl = input.split(/\s/ig);
            return spl.splice(0,lim).join(' ')+'...';
        }
        if(type == 'letter'){
            spl = input.split('');
            return spl.splice(0,lim).join('')+'...';
        }
    }
});