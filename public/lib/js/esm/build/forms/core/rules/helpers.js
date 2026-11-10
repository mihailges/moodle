const e=n=>n==null||typeof n=="string"&&n.trim()===""||Array.isArray(n)&&n.length===0,o=(n,r)=>t=>typeof t!="string"||t===""||n(t)||r;export{e as isEmpty,o as stringRule};
