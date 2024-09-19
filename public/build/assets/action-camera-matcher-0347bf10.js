let h={};const L=(e,s)=>h[e]=s,b=e=>h[e],q=e=>b(e)!==void 0,A=()=>{let e=0;const s=document.querySelectorAll("[data-question-block-idx]"),r=document.querySelectorAll('[id^="form-question-block-"]'),i=document.querySelector("[data-results]"),d=document.querySelector("[data-classer-billboard]"),g=document.querySelector("[data-submit]"),u=document.querySelector("[data-reset]"),c=document.querySelectorAll("[data-next-question]"),l=document.querySelectorAll("[data-previous-question]"),a=document.querySelectorAll("input[type=radio]");[...c,...l].forEach(t=>t.addEventListener("click",n=>{n.preventDefault();const o=t.getAttribute("data-next-question")!==null,m=o?e+1:e-1;if(o&&!q(e)){alert("Please select an answer before proceeding");return}p(m)})),g.addEventListener("click",t=>{t.preventDefault();const n=Object.entries(questionnaire.weights),o=S(n,h);f(o),E(h)}),a.forEach(t=>t.addEventListener("change",n=>{const o=parseInt(n.target.value);L(e,o)})),u.addEventListener("click",t=>{t.preventDefault(),h={},e=0,s.forEach(n=>n.classList.add("hidden")),r[e].classList.remove("hidden"),i.classList.add("hidden"),d.classList.add("hidden"),document.querySelectorAll("input[type=radio]").forEach(n=>{n.checked=!1})});const p=t=>{e=t,s.forEach((n,o)=>o===t?n.classList.remove("hidden"):n.classList.add("hidden"))},f=t=>{const n=Array.from({length:t.length});i.querySelector("ul").innerHTML=n.map((o,m)=>I(m)).join(""),setTimeout(()=>{i.querySelector("ul").innerHTML=t.map(v).join("");const o=i.querySelectorAll(".benefits-list");i.querySelectorAll("[data-toggle-open]").forEach(m=>{m.addEventListener("click",w=>{const x=w.target.getAttribute("data-toggle-open");o.forEach((y,k)=>k===parseInt(x)?y.classList.toggle("hidden"):y.classList.add("hidden"))})})},Math.floor(Math.random()*1e3)+1400),d.classList.remove("hidden"),i.classList.remove("hidden"),d.classList.remove("hidden"),s.forEach(o=>o.classList.add("hidden"))};r[e].classList.remove("hidden")};window.addEventListener("load",A);const S=(e,s)=>{const r=e.reduce((c,[l,a])=>{const f=a.map((t,n)=>t[s[n]]).reduce((t,n)=>t+n,0);return{...c,[l]:f}},{}),i=Object.entries(r),d=i.map(([,c])=>c),g=Math.max(...d),u=Math.min(...d);return i.map(([c,l])=>{const a=g!==u?(l-u)/(g-u)*100:0;return{key:c,value:l,percentage:a,recommendationKey:j(a),recommendation:M(a),color:B(a)}})},E=e=>{fetch("http://127.0.0.1:8000/api/site/actions-camera-matcher",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({answers:e})}).then(r=>r.json()).then(r=>{console.log("Success:",r)}).catch(r=>{console.error("Error:",r)})},B=e=>e>80?"green":e>60?"orange":e>40?"yellow":"red",j=e=>e>80?"highly-recommended":e>60?"good-match":e>40?"might-like":"not-recommended",M=e=>e>80?"Highly recommended!":e>60?"It's a good match!":e>40?"You can might like it!":"Not recommended!",R=e=>`
    <button data-toggle-open="${e}" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-500 pointer-events-none"
            fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>`,$=({key:e,recommendation:s})=>`<div class="flex-1 min-w-0">
        <p class="text-md font-bold text-gray-700 truncate dark:text-white">
            ${e}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            ${s}
        </p>
    </div>`,T=()=>`<ul class="benefits-list hidden grid grid-cols-2">
        ${["Individual configuration","No setup, or hidden fees","Team size: 1 developer","Premium support: 6 months"].map(s=>`
            <li class="flex items-center space-x-3 rtl:space-x-reverse">
                <svg class="flex-shrink-0 w-3.5 h-3.5 text-green-500 dark:text-green-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 12">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5.917 5.724 10.5 15 1.5"/>
                </svg>
                <span>${s}</span>
            </li>
        `).join("")}
    </ul>`,v=(e,s,r=!0)=>`<li class="recommendation-item ${e.recommendationKey}">
    <div class="flex items-center space-x-4">
    <div class="indicator"></div>
        ${$(e)}
        ${r?R(s):""}
    </div>
    ${T()}
</li>`,I=e=>v({key:"Loading...",recommendation:"Just a moment",recommendationKey:"highly-recommended",color:"green"},e,!1);
