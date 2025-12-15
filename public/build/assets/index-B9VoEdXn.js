import{a as d}from"./createLucideIcon-iWXofaGH.js";import{r as b}from"./app-CQ-G37p-.js";import{f as n}from"./index-B9C1SM0Q.js";/**
 * @license lucide-react v0.460.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const y=d("Check",[["path",{d:"M20 6 9 17l-5-5",key:"1gmf2c"}]]);function g(r){const[h,e]=b.useState(void 0);return n(()=>{if(r){e({width:r.offsetWidth,height:r.offsetHeight});const f=new ResizeObserver(o=>{if(!Array.isArray(o)||!o.length)return;const c=o[0];let i,t;if("borderBoxSize"in c){const s=c.borderBoxSize,a=Array.isArray(s)?s[0]:s;i=a.inlineSize,t=a.blockSize}else i=r.offsetWidth,t=r.offsetHeight;e({width:i,height:t})});return f.observe(r,{box:"border-box"}),()=>f.unobserve(r)}else e(void 0)},[r]),h}export{y as C,g as u};
