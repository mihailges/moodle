const r=t=>t==null||typeof t=="string"&&t.trim()===""||Array.isArray(t)&&t.length===0,o=(t,e)=>n=>typeof n!="string"||n===""||t(n)||e;export{r as isEmpty,o as stringRule};
