.SUFFIXES: .svg

BATIK = batik-rasterizer
FILES = status-unknown-trig.svg status-unknown-untrig.svg status-good-trig.svg status-good-untrig.svg status-warning-trig.svg status-warning-untrig.svg status-critical-trig.svg status-critical-untrig.svg viewgraph-on.svg viewgraph-off.svg

all : $(FILES)
	@$(BATIK) -h 12 arrow.svg
	@$(BATIK) -h 15 disk.svg
	@$(BATIK) -h 15 viewgraph-on.svg
	@$(BATIK) -h 15 viewgraph-off.svg
	@$(BATIK) -h 35 -d netmrg-logo-small.png netmrg-logo.svg
	@$(BATIK) -h 100 -d netmrg-logo-medium.png netmrg-logo.svg
	@$(BATIK) -h 250 -d netmrg-logo-large.png netmrg-logo.svg

status:
	@$(MAKE) -s createstatus C1="#ff0000" C2="#ff4c4c" NAME="status-critical-trig.svg"
	@$(MAKE) -s createstatus C1="#ff0000" C2="#7f4c4c" NAME="status-critical-untrig.svg"
	@$(MAKE) -s createstatus C1="#009000" C2="#009000" NAME="status-good-trig.svg"
	@$(MAKE) -s createstatus C1="#009000" C2="#002000" NAME="status-good-untrig.svg"
	@$(MAKE) -s createstatus C1="#0000bf" C2="#4c4cc0" NAME="status-unknown-trig.svg"
	@$(MAKE) -s createstatus C1="#0000bf" C2="#4c4c7f" NAME="status-unknown-untrig.svg"
	@$(MAKE) -s createstatus C1="#e6e600" C2="#e6e500" NAME="status-warning-trig.svg"
	@$(MAKE) -s createstatus C1="#e6e600" C2="#999900" NAME="status-warning-untrig.svg"

viewgraph:
	@cp -f viewgraph.svg viewgraph_on.svg
	@cat viewgraph.svg | perl -ne 's/stroke:#e67f00;/stroke:#000000;/; s/fill:#e67f00;/fill:#000000;/; print;' > viewgraph_off.svg

createstatus:
	@cat status.svg | perl -ne 's/#7f7f7f/$(C1)/; s/#4c4c4c/$(C2)/; print;' > $(NAME)

%.svg: status viewgraph
	@$(BATIK) -h 15 $@

clean:
	rm -f $(FILES)
