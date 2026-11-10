import{isEmpty as m}from"./helpers";import{markMandatory as n}from"../useMoodleFormField";const f=(o,r)=>n((t,i)=>!o(i)||!m(t)||r,o);export{f as requiredIf};
