"use strict";

const API_BASE = "/api";

const qs = sel => document.querySelector(sel);
const qsa = sel => Array.from(document.querySelectorAll(sel));

async function jsonFetch(url, options = {}) {
  try {
    const res = await fetch(url, options);
    const text = await res.text();
    try { return JSON.parse(text); } 
    catch { console.error("Réponse non JSON :", text); return { ok: false, error: "Réponse serveur invalide" }; }
  } catch (err) { console.error("Erreur réseau :", err); return { ok: false, error: "Erreur réseau" }; }
}

async function jsonPost(url, data) {
  return await jsonFetch(url, { method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(data) });
}

async function loadAll() {
  const profile = await jsonFetch(`${API_BASE}/profile.php`); if(profile.ok) fillProfileForm(profile.profile);
  const skills = await jsonFetch(`${API_BASE}/skills.php`); if(skills.ok) renderSkills(skills.skills||[]);
  const exp = await jsonFetch(`${API_BASE}/experiences.php`); if(exp.ok) renderExperiences(exp.experiences||[]);
  const projects = await jsonFetch(`${API_BASE}/projects.php`); if(projects.ok) renderProjects(projects.projects||[]);
}

function fillProfileForm(profile) {
  if(!profile) return;
  const f = qs("#profileForm");
  ["fullname","title","email","location","phone","linkedin","about"].forEach(k => { if(f[k]) f[k].value = profile[k]||""; });
}

qs("#profileForm")?.addEventListener("submit", async e => {
  e.preventDefault();
  const f = e.target;
  const data = ["fullname","title","email","location","phone","linkedin","about"].reduce((obj,k)=>{obj[k]=f[k].value.trim();return obj;},{});
  qs("#profileMsg").textContent="Enregistrement...";
  const res = await jsonPost(`${API_BASE}/save_profile.php`, data);
  qs("#profileMsg").textContent = res.ok ? "Enregistré" : "Erreur: "+(res.error||"unknown");
  if(res.ok) setTimeout(()=>qs("#profileMsg").textContent="",1200);
  loadAll();
});

function renderSkills(skills){
  const ul = qs("#skillsList"); if(!ul) return; ul.innerHTML="";
  skills.forEach(s=>{
    const li=document.createElement("li");
    li.innerHTML=`<div><strong>${escapeHtml(s.name)}</strong><div class="meta">${escapeHtml(s.category)} • ${escapeHtml(s.meta||"")}</div><div class="meta">Valeur: ${escapeHtml(s.value)}</div></div>
    <div class="small-actions"><button class="edit" data-id="${s.id}">✎</button><button class="delete" data-id="${s.id}">🗑</button></div>`;
    ul.appendChild(li);
  });
  qsa("#skillsList .delete").forEach(b=>b.onclick=async()=>{if(!confirm("Supprimer cette compétence ?")) return; const res=await jsonPost(`${API_BASE}/delete_skill.php`,{id:b.dataset.id}); if(res.ok) loadAll(); else alert(res.error||"Erreur");});
  qsa("#skillsList .edit").forEach(b=>b.onclick=async()=>{const name=prompt("Nouveau nom:"); if(!name) return; const res=await jsonPost(`${API_BASE}/save_skill.php`,{id:b.dataset.id,name}); if(res.ok) loadAll(); else alert(res.error||"Erreur");});
}

qs("#addSkillForm")?.addEventListener("submit", async e => {
  e.preventDefault(); const f = e.target;
  const data = ["category","name","value","meta"].reduce((obj,k)=>{obj[k]=f[k].value.trim();return obj;},{});
  qs("#skillMsg").textContent="Ajout...";
  const res = await jsonPost(`${API_BASE}/save_skill.php`, data);
  qs("#skillMsg").textContent=res.ok?"Ajouté":"Erreur: "+(res.error||"");
  if(res.ok){ f.reset(); loadAll(); setTimeout(()=>qs("#skillMsg").textContent="",1200); }
});

function renderExperiences(list){
  const ul = qs("#experiencesList"); if(!ul) return; ul.innerHTML="";
  list.forEach(it=>{
    const li=document.createElement("li");
    li.innerHTML=`<div><strong>${escapeHtml(it.title)}</strong> — <em>${escapeHtml(it.company||"")}</em><div class="meta">${escapeHtml(it.start_date)}${it.end_date?" – "+escapeHtml(it.end_date):""}</div><div class="meta">${escapeHtml(it.summary||"")}</div></div>
    <div class="small-actions"><button class="edit" data-id="${it.id}">✎</button><button class="delete" data-id="${it.id}">🗑</button></div>`;
    ul.appendChild(li);
  });
  qsa("#experiencesList .delete").forEach(b=>b.onclick=async()=>{if(!confirm("Supprimer expérience ?")) return; const res=await jsonPost(`${API_BASE}/delete_experience.php`,{id:b.dataset.id}); if(res.ok) loadAll(); else alert(res.error||"Erreur");});
  qsa("#experiencesList .edit").forEach(b=>b.onclick=async()=>{const title=prompt("Nouveau titre:"); if(!title) return; const res=await jsonPost(`${API_BASE}/save_experience.php`,{id:b.dataset.id,title}); if(res.ok) loadAll(); else alert(res.error||"Erreur");});
}

qs("#addExperienceForm")?.addEventListener("submit", async e => {
  e.preventDefault(); const f=e.target;
  const data = ["start_date","end_date","title","company","summary","details"].reduce((obj,k)=>{obj[k]=f[k].value.trim();return obj;},{});
  qs("#expMsg").textContent="Ajout...";
  const res=await jsonPost(`${API_BASE}/save_experience.php`,data);
  qs("#expMsg").textContent=res.ok?"Ajouté":"Erreur: "+(res.error||"");
  if(res.ok){ f.reset(); loadAll(); setTimeout(()=>qs("#expMsg").textContent="",1200); }
});

function renderProjects(list){
  const ul=qs("#projectsList"); if(!ul) return; ul.innerHTML="";
  list.forEach(p=>{
    const li=document.createElement("li");
    li.innerHTML=`<div><strong>${escapeHtml(p.title)}</strong><div class="meta">${escapeHtml(p.description||"")}</div><div class="meta">Tags: ${escapeHtml(p.tags||"")}</div></div>
    <div class="small-actions"><button class="edit" data-id="${p.id}">✎</button><button class="delete" data-id="${p.id}">🗑</button></div>`;
    ul.appendChild(li);
  });
  qsa("#projectsList .delete").forEach(b=>b.onclick=async()=>{if(!confirm("Supprimer projet ?")) return; const res=await jsonPost(`${API_BASE}/delete_project.php`,{id:b.dataset.id}); if(res.ok) loadAll(); else alert(res.error||"Erreur");});
  qsa("#projectsList .edit").forEach(b=>b.onclick=async()=>{const title=prompt("Nouveau titre:"); if(!title) return; const res=await jsonPost(`${API_BASE}/save_project.php`,{id:b.dataset.id,title}); if(res.ok) loadAll(); else alert(res.error||"Erreur");});
}

qs("#addProjectForm")?.addEventListener("submit", async e=>{ e.preventDefault(); const f=e.target;
  const data = ["title","description","tags"].reduce((obj,k)=>{obj[k]=f[k].value.trim();return obj;},{});
  qs("#projMsg").textContent="Ajout...";
  const res=await jsonPost(`${API_BASE}/save_project.php`,data);
  qs("#projMsg").textContent=res.ok?"Ajouté":"Erreur: "+(res.error||"");
  if(res.ok){ f.reset(); loadAll(); setTimeout(()=>qs("#projMsg").textContent="",1200); }
});

function escapeHtml(s){if(s==null) return ""; return String(s).replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));}

qs("#reloadBtn")?.addEventListener("click", loadAll);

loadAll();
