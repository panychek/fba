# FBA Fulfillment Outbound Shipment Service

This service could be used for shipping your orders with the Amazon MWS API.

**To get the things running just make the "init" target:**

`make init` 

This will:
1. Run a containerized application
2. Install the dependencies
3. Make static analysis and perform the testing right off the bat

*The tests are isolated from the actual MWS API and its responses are mocked.*