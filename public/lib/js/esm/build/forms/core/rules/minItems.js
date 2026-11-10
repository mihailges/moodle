import{isEmpty as o}from"./helpers";import{markMandatory as i}from"../useMoodleFormField";const y=(r,m)=>i(t=>!Array.isArray(t)||t.filter(e=>!o(e)).length>=r||m,r>=1);export{y as minItems};
