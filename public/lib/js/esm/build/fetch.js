import c from"@moodle/lms/core/config";import m from"@moodle/lms/core/pending";import{getGlobalAbortSignal as d}from"./abort";/**
 * The core/fetch module allows you to make web service requests to the Moodle REST API.
 *
 * @module     core/fetch
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @example <caption>Perform a single GET request</caption>
 * import Fetch from 'core/fetch';
 *
 * const result = Fetch.performGet('mod_example', 'animals', { params: { type: 'mammal' } });
 *
 * result.then((response) => {
 *    // Do something with the Response object.
 * })
 * .catch((error) => {
 *     // Handle the error
 * });
 */class h{#e;#s;#t;#r;constructor(e){this.#e=e,this.#s=new Promise((s,t)=>{this.#t=s,this.#r=t})}get request(){return this.#e}get promise(){return this.#s}handleResponse(e,s=!1){e.ok?this.#t(e):this.#r(s?e:e.statusText)}}class R{static async request(e,s,{cachekey:t=null,headers:r={},params:n={},body:i=null,method:u="GET",rejectWithResponse:a=!1}={}){const p=new m(`Requesting ${e}/${s} with ${u}`),o=R.#s(R.#e(e),s,{headers:r,params:n,method:u,body:i,cachekey:t}),l=await fetch(o.request);return p.resolve(),o.handleResponse(l,a),o.promise}static performGet(e,s,{cachekey:t=null,headers:r={},params:n={}}={}){return this.request(e,s,{cachekey:t,headers:r,params:n,method:"GET"})}static performHead(e,s,{headers:t={},params:r={}}={}){return this.request(e,s,{headers:t,params:r,method:"HEAD"})}static performPost(e,s,{headers:t={},body:r,rejectWithResponse:n}){return this.request(e,s,{headers:t,body:r,method:"POST",rejectWithResponse:n})}static performPut(e,s,{headers:t={},body:r,rejectWithResponse:n}){return this.request(e,s,{headers:t,body:r,method:"PUT",rejectWithResponse:n})}static performPatch(e,s,{headers:t={},body:r,rejectWithResponse:n}){return this.request(e,s,{headers:t,body:r,method:"PATCH",rejectWithResponse:n})}static performDelete(e,s,{headers:t={},params:r={},body:n=null}={}){return this.request(e,s,{headers:t,body:n,params:r,method:"DELETE"})}static#e(e){return e.replace(/^core_/,"")}static#s(e,s,{cachekey:t=null,headers:r={},params:n={},body:i=null,method:u="GET"}){const a=["rest","v2"];t&&t>1&&a.push(`cachekey:${t}`),a.push(e,s);const p=new URL(`${c.apibase}/${a.join("/").replaceAll("//","/")}`),o={method:u,headers:{...r,Accept:"application/json","Content-Type":"application/json",pageparent:c.traceId||""},signal:d()};return Object.entries(n).forEach(([l,g])=>{p.searchParams.append(l,g)}),i&&(i instanceof FormData?o.body=i:typeof i=="object"?o.body=JSON.stringify(i):o.body=i),new h(new Request(p,o))}}export{R as default};
