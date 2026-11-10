import{isEmpty as i}from"./helpers";import{markMandatory as n}from"../formField";const f=(o,r)=>n((t,m)=>!o(m)||!i(t)||r,o);export{f as requiredIf};
