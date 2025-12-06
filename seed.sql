-- seed.sql
USE cv_db;

INSERT INTO profile (fullname,title,email,location,phone,linkedin,about)
VALUES
('Azza Salmouk','Élève ingénieure en informatique','azzasalmouk20@gmail.com','Sousse, Tunisie','+216 54 303 136','https://www.linkedin.com/in/azza-salmouk-7908482a5','Élève ingénieure passionnée par le développement web et les systèmes embarqués.');

INSERT INTO skills (category,name,value,meta,ord) VALUES
('web','HTML / CSS',95,'Mark-up, responsive, accessibilité',1),
('web','JavaScript',85,'DOM, fetch, ES6+',2),
('mobile','Flutter',70,'UI mobile, state management',3),
('langages','Python',80,'Scripts, OpenCV',4);

INSERT INTO experiences (start_date,end_date,title,company,summary,details,ord) VALUES
('07/2025','08/2025','Stage d’été','Société Régionale de Transport de Médenine','Refonte site + GTFS','Gestion des horaires, intégration GTFS, tests',1),
('02/2024','05/2024','Stage PFE','Chaaben Technology Group','Irrigation automatisée','IoT, capteurs, dashboard web',2);

INSERT INTO projects (title,description,tags,ord) VALUES
('Refonte du site SRTM','Site moderne + intégration GTFS','Vue.js,Laravel,Tailwind',1),
('VORTEXBID','Application mobile d’enchères automobiles','Flutter,Figma',2);
