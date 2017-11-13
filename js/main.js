/**
* @author Mücahid Dayan
* 
*/


let headers = {
    'X-WP-Nonce':veganRezept.nonce,
    'Content-Type':'application/json'
}

let request = {
    credentials: 'include',
    headers
}
Object.freeze(request);



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
    var req = Object.assign({},request);
    req.body = JSON.stringify(recipe);
    req.method = 'post';
    return fetch(`${location.origin}/wp-json/wp/v2/recipes`,req)
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
    var req = Object.assign({},request);
    req.body = JSON.stringify(recipe);
    req.method = 'POST';
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${recipe.id}`,req)
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
    var req = Object.assign({},request);
    req.method = 'DELETE';
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${id}`,req)
    .catch(err=>console.log(err));
}

/**
* get all recipes
* @returns {Promise}
*/
let getRecipes = ()=>{
    return fetch(`${location.origin}/wp-json/wp/v2/recipes`,request)
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
    return fetch(`${location.origin}/wp-json/wp/v2/recipes/${id}`,request)
    .catch(err=>console.log(err));
}

let getRecipesFromMyBook = ()=>{    
    console.log(request);
    return fetch(`${location.origin}/wp-json/wp/v2/mybook`,request).catch(err=>console.log(err));
}

let addToMyBook = (recipeID)=>{
    if(!recipeID){
        console.warn('Recipe ID can not be empty');
        return;
    }
    var req = Object.assign({},request);
    req.method = 'POST';
    req.body = JSON.stringify({recipeID});
        console.log(req);
    return fetch(`${location.origin}/wp-json/wp/v2/mybook/`,req)    
    .catch(err=>console.log(err))
}

/**
* deletes recipe with given id
* @param {number} id
* @returns {Promise}
*/
let removeFromMyBook = (id)=>{
    let data = {};
    if(id){
        data.recipeID = id;
    }
    var req = Object.assign({},request);
    req.method = 'DELETE';
    req.body = JSON.stringify(data);
    return fetch(`${location.origin}/wp-json/wp/v2/mybook`,req)
    .catch(err=>console.log(err));
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

let isLiked = (recipe)=>{
    if(typeof recipe !== 'object'){
        console.warn('param must be Recipe Object');
        return;
    }
    return veganRezept.book.includes(recipe.ID);
}

//################  ANGULARJS ########################
let app = angular.module('app',[]);

app.controller('mainController',['$scope',($scope)=>{
    $scope.title = "Rezepte";
}]);


// DIRECTIVES
app.directive('recipes',()=>{
    let link = (scope,el,attrs)=>{
        console.log(scope.rType);
        scope.recipes = scope.rType === 'book'?JSON.parse(veganRezept.bookContent):JSON.parse(veganRezept.recipes);
        scope.defaultImg = `${veganRezept.pluginDirUrl}/img/salad.png`;
        // console.log(scope.recipes);

        scope.liked = veganRezept.book;

        scope.isLiked = (rec)=>{            
            if(typeof rec !== 'object'){
                console.warn('param must be Recipe Object');
                return;
            }
            return scope.liked.includes(rec.ID);
        }

        scope.dislike = (rec,index)=>{
            console.log(rec.ID);
            removeFromMyBook(rec.ID).catch(err=>console.warn(err));
            // let index = scope.liked.indexOf(rec.ID);
            scope.liked = remove(scope.liked,rec.ID);
            if(scope.rType === 'book'){
                scope.recipes.splice(index,1);
            }
        }

        let remove = (arr,id)=>{
            let index = arr.indexOf(id);
            arr.splice(index,1);
            return arr;
        }

        scope.like = (rec) =>{
            console.log(rec.ID);
            addToMyBook(rec.ID).catch(err=>console.warn(err));
            scope.liked.push(rec.ID);
        }
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
        <div class="like-dislike">
            <i ng-click="like(recipe)" title="like" ng-if="!isLiked(recipe)" class="fa fa-thumbs-up" aria-hidden="true"></i>
            <i ng-click="dislike(recipe,$index)" title="dislike" ng-if="isLiked(recipe)" class="fa fa-thumbs-up liked" aria-hidden="true"></i>
        </div>
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
            rType  : '@',
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