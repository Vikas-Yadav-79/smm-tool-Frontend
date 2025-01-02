import { bootstrapApplication } from '@angular/platform-browser';
import { provideRouter } from '@angular/router';
import { AppComponent } from './app/app.component';
import { AppRoutingComponent } from './app/app.routes';
import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';
import { appConfig } from './app/app.config';

bootstrapApplication(AppRoutingComponent, appConfig)
  .catch(err => console.error(err));
platformBrowserDynamic()
  .bootstrapModule(AppRoutingComponent)
  .catch(err => console.error(err));
