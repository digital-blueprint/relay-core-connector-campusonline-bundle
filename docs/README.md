# Overview

The Core Connector CAMPUSonline provides an _Authorization Data Provider_ which retrieves user attributes
used for access control from CAMPUSonline.

## Configuration

```yaml
# Default configuration for extension with alias: "dbp_relay_core_connector_campusonline"
dbp_relay_core_connector_campusonline:
  campus_online:
    # The base URL of the CO instance
    base_url:             ~
    # The ID of the client (client credentials flow)
    client_id:            ~
    # The client secret for the client referenced by client_id
    client_secret:        ~
  organization_ids:
    # Prototype
    -
      # The attribute name this list will be stored in
      name:                 ~ # Example: all_ids
      # A filter expression. Gets the "org" object as input and should return false to skip organizations
      filter:               ~ # Example: 'true'

```
 