let h={};const S=questionnaire.benefits,A=(e,o)=>h[e]=o,k=e=>h[e],w=e=>k(e)!==void 0,B=()=>{let e=0;const o=document.querySelectorAll("[data-question-block-idx]"),i=document.querySelectorAll('[id^="form-question-block-"]'),r=document.querySelector("[data-results]"),c=document.querySelector("[data-classer-billboard]"),g=document.querySelector("[data-submit]"),u=document.querySelector("[data-reset]"),p=document.querySelectorAll("[data-next-question]"),l=document.querySelectorAll("[data-previous-question]"),d=document.querySelectorAll("input[type=radio]");[...p,...l].forEach(s=>s.addEventListener("click",t=>{t.preventDefault();const n=s.getAttribute("data-next-question")!==null,m=n?e+1:e-1;if(n&&!w(e)){alert("Please select an answer before proceeding");return}a(m)})),g.addEventListener("click",s=>{s.preventDefault();const t=Object.entries(questionnaire.weights),n=M(t,h);f(n),T(h)}),d.forEach(s=>s.addEventListener("change",t=>{const n=parseInt(t.target.value);A(e,n)})),u.addEventListener("click",s=>{s.preventDefault(),h={},e=0,o.forEach(t=>t.classList.add("hidden")),i[e].classList.remove("hidden"),r.classList.add("hidden"),c.classList.add("hidden"),document.querySelectorAll("input[type=radio]").forEach(t=>{t.checked=!1})});const a=s=>{e=s,o.forEach((t,n)=>n===s?t.classList.remove("hidden"):t.classList.add("hidden"))},f=s=>{const t=Array.from({length:s.length});r.querySelector("ul").innerHTML=t.map((n,m)=>K(m)).join(""),setTimeout(()=>{r.querySelector("ul").innerHTML=s.map(q).join("");const n=r.querySelectorAll(".benefits-list");r.querySelectorAll("[data-toggle-open]").forEach(m=>{m.addEventListener("click",b=>{const v=b.target.getAttribute("data-toggle-open");n.forEach((L,E)=>E===parseInt(v)?L.classList.toggle("hidden"):L.classList.add("hidden"))})})},Math.floor(Math.random()*1e3)+1400),c.classList.remove("hidden"),r.classList.remove("hidden"),c.classList.remove("hidden"),o.forEach(n=>n.classList.add("hidden"))};i[e].classList.remove("hidden")};window.addEventListener("load",B);const M=(e,o)=>{const i=e.reduce((l,[d,a])=>{const f=a.map((t,n)=>t[o[n]]);if(f.includes("out"))return l;const s=f.reduce((t,n)=>t+n,0);return{...l,[d]:s}},{}),r=Object.entries(i),c=r.map(([,l])=>l),g=Math.max(...c),u=Math.min(...c);return r.map(([l,d])=>{const a=g!==u?(d-u)/(g-u)*100:0;return{key:l,value:d,percentage:a,recommendationKey:I(a),recommendation:R(a),color:x(a)}})},T=e=>{fetch("https://classermedia.com/api/site/actions-camera-matcher",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({answers:e,grc:document.querySelector("#grc-token").value})}).then(()=>{})},x=e=>e>80?"green":e>60?"orange":e>40?"yellow":"red",I=e=>e>80?"highly-recommended":e>50?"good-match":"might-like",R=e=>e>80?"Highly recommend!":e>60?"It's a good match!":"You might like it!",H=e=>y(document.getElementById("template-acm-results-toggle-benefits-button").innerHTML,{index:e}),j=e=>y(document.getElementById("template-acm-results-title").innerHTML,e),O=e=>{const o=S[e]||[],i=document.getElementById("template-acm-results-benefits-item").innerHTML;return`<ul class="benefits-list hidden grid grid-cols-2">
        ${o.map(r=>y(i,{benefit:r})).join("")}
    </ul>`},q=(e,o,i=!0)=>{const r=document.getElementById("template-acm-results-item").innerHTML,c={title:j(e),benefits:O(e.key),recommendationKey:e.recommendationKey,toggleBenefitsStateButton:i?H(o):""};return y(r,c)},K=e=>q({key:"Fetching results...",recommendation:"Just a moment",recommendationKey:"might-like",color:"green"},e,!1),y=(e,o)=>e.replace(/\${(.*?)}/g,(i,r)=>o[r.trim()]);