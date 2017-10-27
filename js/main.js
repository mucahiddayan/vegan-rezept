/**
* @author Mücahid Dayan
* 
*/

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
    .then(e=>e.json());
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
    return fetch(`${location.origin}/wp-json/wp/v2/recipes`,{
        method:'update',
        credentials: 'include',
        headers:{
            'X-WP-Nonce':veganRezept.nonce,
            'Content-Type':'application/json'
        },
        body:JSON.stringify(recipe),
    })
    .then(e=>e.json());
}

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
    .then(e=>e.json());
}

let getRecipes = ()=>{
    return fetch(`${location.origin}/wp-json/wp/v2/recipes`,{
        headers:{
            'Content-Type':'application/json'
        }
    })
    .then(e=>e.json());
}

let getRecipe = (id)=>{
    if(!id){
        console.warn('ID is required');
        return;
    }
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${id}`,{
        headers:{
            'Content-Type':'application/json'
        }
    })
    .then(e=>e.json());
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
        scope.defaultImg = `${veganRezept.pluginDirUrl}/img/icon_20.png`;
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
                    <img ng-src="{{recipe.img_url?recipe.img_url:defaultImg}}" alt="{{recipe.post_title}}"/>
                </div>
                <a target="_blank" rel="nofollow" href="{{recipe.guid}}">
                    <span ng-bind="recipe.post_title"></span>
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