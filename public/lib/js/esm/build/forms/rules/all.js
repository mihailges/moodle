const l=(...e)=>async(r,u)=>{for(const o of e){const t=await o(r,u);if(t!==!0)return t}return!0};export{l as all};
