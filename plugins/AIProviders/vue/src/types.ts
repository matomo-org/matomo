/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

export interface ProviderConfiguration {
  apiKey: string;
  endpointUrl: string;
  model: string;
  useFipsEndpoint: boolean;
}

export interface Provider {
  id: string;
  name: string;
  description: string;
  supportsCustomEndpoint: boolean;
  supportsFipsEndpoint: boolean;
  defaultEndpointUrl: string;
  endpointFieldTitle: string;
  endpointFieldPlaceholder: string;
  defaultModel: string;
  configuration: {
    hasApiKey: boolean;
    endpointUrl: string;
    model: string;
    useFipsEndpoint: boolean;
    isUsable: boolean;
  };
}

export interface CapabilityLevel {
  label: string;
  description?: string;
}

export interface Settings {
  defaultProviderId: string;
  defaultCapabilityLevel: string;
  canEditProviderConfiguration: boolean;
  canEditCapabilityLevel: boolean;
  capabilityLevels: Record<string, CapabilityLevel>;
  providers: Provider[];
}

export interface CapabilityLevelOption {
  id: string;
  label: string;
  description: string;
}

export interface TestConnectionResponse {
  providerId: string;
  providerName: string;
  models: string[];
}
