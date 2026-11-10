import{isEmpty as e}from"./helpers";import{markMandatory as i}from"../formField";const f=(r,m)=>i(t=>!Array.isArray(t)||t.filter(o=>!e(o)).length>=r||m,r>=1);export{f as minItems};
