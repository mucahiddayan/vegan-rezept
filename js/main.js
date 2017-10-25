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
let editRecipe = (id,recipe)=>{
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