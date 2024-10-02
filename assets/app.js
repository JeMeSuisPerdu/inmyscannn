import { Application } from "stimulus";
import { definitionsFromContext } from "stimulus/webpack-helpers";
import * as Turbo from '@hotwired/turbo';
import './styles/css/app.css';

// Initialiser Stimulus
window.Stimulus = Application.start();
const context = require.context("./controllers", true, /\.js$/);
Stimulus.load(definitionsFromContext(context));

// Initialiser Turbo
Turbo.setProgressBarDelay(100); // Juste un exemple de configuration Turbo
Turbo.session.drive = false; // Désactive Turbo Drive par défaut
Turbo.start();