<!DOCTYPE html>
<html>
<head>
    <title>TelegramGame</title>
    <meta name="description" content="Движок по созданию игровых квестов в Telegram"/>
    <!-- Copyright 1998-2016 by Northwoods Software Corporation. -->
    <meta charset="UTF-8">
    <script src="js/go.js"></script>
    <script src="js/jquery.min.js"></script>

    <script id="code">
        function init() {
            if (window.goSamples) goSamples();  // init for these samples -- you don't need to call this
            var $ = go.GraphObject.make;  // for conciseness in defining templates

            myDiagram =
                $(go.Diagram, "myDiagramDiv",  // must name or refer to the DIV HTML element
                    {
                        initialContentAlignment: go.Spot.Center,
                        allowDrop: true,  // must be true to accept drops from the Palette
                        "LinkDrawn": showLinkLabel,  // this DiagramEvent listener is defined below
                        "LinkRelinked": showLinkLabel,
                        "animationManager.duration": 800, // slightly longer than default (600ms) animation
                        "undoManager.isEnabled": true  // enable undo & redo
                    });

            function nodeStyle() {
                return [
                    // The Node.location comes from the "loc" property of the node data,
                    // converted by the Point.parse static method.
                    // If the Node.location is changed, it updates the "loc" property of the node data,
                    // converting back using the Point.stringify static method.
                    new go.Binding("location", "loc", go.Point.parse).makeTwoWay(go.Point.stringify),
                    {
                        // the Node.location is at the center of each node
                        locationSpot: go.Spot.Center,
                        //isShadowed: true,
                        //shadowColor: "#888",
                        // handle mouse enter/leave events to show/hide the ports
                        mouseEnter: function (e, obj) {
                            showPorts(obj.part, true);
                        },
                        mouseLeave: function (e, obj) {
                            showPorts(obj.part, false);
                        }
                    }
                ];
            }

            // Define a function for creating a "port" that is normally transparent.
            // The "name" is used as the GraphObject.portId, the "spot" is used to control how links connect
            // and where the port is positioned on the node, and the boolean "output" and "input" arguments
            // control whether the user can draw links from or to the port.
            function makePort(name, spot, output, input) {
                // the port is basically just a small circle that has a white stroke when it is made visible
                return $(go.Shape, "Circle",
                    {
                        fill: "transparent",
                        stroke: null,  // this is changed to "white" in the showPorts function
                        desiredSize: new go.Size(8, 8),
                        alignment: spot, alignmentFocus: spot,  // align the port on the main Shape
                        portId: name,  // declare this object to be a "port"
                        fromSpot: spot, toSpot: spot,  // declare where links may connect at this port
                        fromLinkable: output, toLinkable: input,  // declare whether the user may draw links to/from here
                        cursor: "pointer"  // show a different cursor to indicate potential link point
                    });
            }

            // define the Node templates for regular nodes

            var lightText = 'whitesmoke';

            myDiagram.nodeTemplateMap.add("",  // the default category
                $(go.Node, "Spot", nodeStyle(),
                    // the main object is a Panel that surrounds a TextBlock with a rectangular Shape
                    $(go.Panel, "Auto",
                        $(go.Shape, "Rectangle",
                            {fill: "#00A9C9", stroke: null},
                            new go.Binding("figure", "figure")),


                        $(go.Panel, "Table",
                            {
                                margin: new go.Margin(6, 10, 0, 3),
                                defaultAlignment: go.Spot.Left
                            },
                            $(go.RowColumnDefinition, {column: 2, width: 4}),

                            $(go.Picture,
                                {column: 1, margin: new go.Margin(0, 0, 5, 0)},
                                {
                                    sourceCrossOrigin: function (pict) {
                                        return "use-credentials";
                                    }
                                },
                                new go.Binding("source", "photo")),


                            $(go.TextBlock, "sleep: ",
                                {row: 2, column: 0}),
                            $(go.TextBlock,
                                {
                                    row: 2, column: 1, columnSpan: 400,
                                    editable: true, isMultiline: false,
                                    minSize: new go.Size(10, 14),
                                    margin: new go.Margin(0, 0, 0, 3)
                                },
                                new go.Binding("text", "sleep").makeTwoWay()),

                            $(go.TextBlock, "message: ",
                                {row: 3, column: 0}),
                            $(go.TextBlock,
                                {
                                    row: 3, column: 1, columnSpan: 4,
                                    editable: true, isMultiline: true,
                                    minSize: new go.Size(10, 14),
                                    margin: new go.Margin(3, 3, 3, 3)
                                },
                                new go.Binding("text", "message").makeTwoWay()),

                            $(go.TextBlock, "photo: ",
                                {row: 4, column: 0}),
                            $(go.TextBlock,
                                {
                                    row: 4, column: 1, columnSpan: 4,
                                    editable: true, isMultiline: true,
                                    minSize: new go.Size(10, 14),
                                    margin: new go.Margin(3, 3, 3, 3)
                                },
                                new go.Binding("text", "photo").makeTwoWay()),


                            $(go.TextBlock, "comment: ",
                                {row: 7, column: 0}),
                            $(go.TextBlock,
                                {
                                    row: 7, column: 1, columnSpan: 4,
                                    editable: true, isMultiline: true,
                                    minSize: new go.Size(10, 14),
                                    margin: new go.Margin(3, 3, 3, 3)
                                },
                                new go.Binding("text", "comment").makeTwoWay())
                        )  // end Table Panel
                    ),
                    // four named ports, one on each side:
                    makePort("T", go.Spot.Top, false, true),
                    makePort("L", go.Spot.Left, true, true),
                    makePort("R", go.Spot.Right, true, true),
                    makePort("B", go.Spot.Bottom, true, false)
                ));

            // replace the default Link template in the linkTemplateMap
            myDiagram.linkTemplate =
                $(go.Link,  // the whole link panel
                    {
                        routing: go.Link.AvoidsNodes,
                        curve: go.Link.JumpOver,
                        corner: 5, toShortLength: 4,
                        relinkableFrom: true,
                        relinkableTo: true,
                        reshapable: true,
                        resegmentable: true,
                        // mouse-overs subtly highlight links:
                        mouseEnter: function (e, link) {
                            link.findObject("HIGHLIGHT").stroke = "rgba(30,144,255,0.2)";
                        },
                        mouseLeave: function (e, link) {
                            link.findObject("HIGHLIGHT").stroke = "transparent";
                        }
                    },
                    new go.Binding("points").makeTwoWay(),
                    $(go.Shape,  // the highlight shape, normally transparent
                        {isPanelMain: true, strokeWidth: 8, stroke: "transparent", name: "HIGHLIGHT"}),
                    $(go.Shape,  // the link path shape
                        {isPanelMain: true, stroke: "gray", strokeWidth: 2}),
                    $(go.Shape,  // the arrowhead
                        {toArrow: "standard", stroke: null, fill: "gray"}),
                    $(go.Panel, "Auto",  // the link label, normally not visible
                        {visible: false, name: "LABEL", segmentIndex: 2, segmentFraction: 0.5},
                        new go.Binding("visible", "visible").makeTwoWay(),
                        $(go.Shape, "RoundedRectangle",  // the label shape
                            {fill: "#F8F8F8", stroke: null}),
                        $(go.TextBlock, "Yes",  // the label
                            {
                                textAlign: "center",
                                font: "40pt helvetica, arial, sans-serif",
                                stroke: "#333333",
                                editable: true
                            },
                            new go.Binding("text").makeTwoWay())
                    )
                );

            // Make link labels visible if coming out of a "conditional" node.
            // This listener is called by the "LinkDrawn" and "LinkRelinked" DiagramEvents.
            function showLinkLabel(e) {
                var label = e.subject.findObject("LABEL");
                if (label !== null) label.visible = (e.subject.fromNode.data.figure === "Diamond");
            }

            // temporary links used by LinkingTool and RelinkingTool are also orthogonal:
            myDiagram.toolManager.linkingTool.temporaryLink.routing = go.Link.Orthogonal;
            myDiagram.toolManager.relinkingTool.temporaryLink.routing = go.Link.Orthogonal;

            load();  // load an initial diagram from some JSON text

            // initialize the Palette that is on the left side of the page
            myPalette =
                $(go.Palette, "myPaletteDiv",  // must name or refer to the DIV HTML element
                    {
                        "animationManager.duration": 800, // slightly longer than default (600ms) animation
                        nodeTemplateMap: myDiagram.nodeTemplateMap,  // share the templates used by myDiagram
                        model: new go.GraphLinksModel([  // specify the contents of the Palette
                            {key: 1, sleep: 0, message: "Новый блок"}
                        ])
                    });

        }

        // Make all ports on a node visible when the mouse is over the node
        function showPorts(node, show) {
            var diagram = node.diagram;
            if (!diagram || diagram.isReadOnly || !diagram.allowLink) return;
            node.ports.each(function (port) {
                port.stroke = (show ? "white" : null);
            });
        }

        // Show the diagram's model in JSON format that the user may edit
        function save() {
            document.getElementById("mySavedModel").value = myDiagram.model.toJson();
            var json = document.getElementById("mySavedModel").value;
            $.ajax({
                type: 'get',
                url: 'scripts/jsonUpdate.php',
                data: {'json': json, "test": "test"},
                response: 'text',
            });
        }
        function load() {
            myDiagram.model = go.Model.fromJson(document.getElementById("mySavedModel").value);
        }
        function to_base() {
            $.ajax({
                type: 'post',
                url: 'scripts/toBase.php',
                response: 'text',
            });
        }
    </script>
</head>
<body onload="init()">
<div id="sample">
    <div style="width:100%; white-space:nowrap;">
    <span style="display: inline-block; vertical-align: top; padding: 5px; width:100px">
      <div id="myPaletteDiv" style="border: solid 1px gray; height: 720px"></div>
    </span>

        <span style="display: inline-block; vertical-align: top; padding: 5px; width:90%">
      <div id="myDiagramDiv" style="border: solid 1px gray; height: 720px"></div>
    </span>
    </div>

    <button id="SaveButton" onclick="save()">Save</button>
    <button id="SaveButton" onclick="load()">Load</button>
    <button id="SaveButton" onclick="to_base()">To Base</button>
    <textarea id="mySavedModel" style="width:100%;height:300px">
	<?php
    echo file_get_contents("json/game.json");
    ?>
  </textarea>
</div>
</body>
</html>